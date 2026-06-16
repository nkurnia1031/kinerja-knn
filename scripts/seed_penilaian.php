<?php

declare(strict_types=1);

const LABEL_PATTERNS = [
    'Sangat Baik' => [4, 4, 4, 4, 3, 4, 4, 3, 4, 4, 4, 3, 4],
    'Baik'        => [3, 3, 3, 3, 3, 2, 3, 3, 3, 3, 3, 2, 3],
    'Cukup'       => [2, 2, 2, 2, 3, 2, 2, 2, 2, 3, 2, 2, 2],
    'Kurang'      => [1, 2, 1, 2, 1, 2, 2, 1, 2, 2, 1, 2, 1],
];

main($argv);

function main(array $argv): void
{
    $options = getopt('', [
        'periode-id::',
        'count::',
        'training-count::',
        'training-percent::',
        'start-year::',
        'seed::',
        'overwrite',
        'list-periods',
        'help',
    ]);

    if (isset($options['help'])) {
        printHelp();
        return;
    }

    $config = loadEnv(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env');
    $pdo = connectDatabase($config);
    $currentYear = (int) date('Y');

    $startYear = isset($options['start-year']) ? (int) $options['start-year'] : null;
    ensurePeriodePenilaian($pdo, $currentYear, $startYear);

    if (isset($options['list-periods'])) {
        listPeriods($pdo);
        return;
    }

    $seed = isset($options['seed']) ? (int) $options['seed'] : 20260420;
    mt_srand($seed);

    $kriteria = getKriteria($pdo);

    if (count($kriteria) === 0) {
        fail("Tidak ada kriteria aktif. Isi tabel kriteria terlebih dahulu.");
    }

    $overwrite = isset($options['overwrite']);

    // Mode multi-periode: semua karyawan dinilai setiap semester mulai start-year s/d semester berjalan.
    if ($startYear !== null) {
        $trainingPercent = isset($options['training-percent']) ? (int) $options['training-percent'] : 80;
        $trainingPercent = max(0, min(100, $trainingPercent));

        seedAllPeriods(
            $pdo,
            $kriteria,
            $startYear,
            $trainingPercent,
            $overwrite
        );

        echo "Seed penilaian (multi-periode) selesai.\n";
        echo "Start year      : {$startYear}\n";
        echo "Training %      : {$trainingPercent}% (random)\n";
        echo "Seed random     : {$seed}\n";
        return;
    }

    $periode = resolvePeriode($pdo, isset($options['periode-id']) ? (int) $options['periode-id'] : null);

    $count = max(1, (int) ($options['count'] ?? 8));
    $trainingCount = max(0, (int) ($options['training-count'] ?? 4));

    $karyawan = getKaryawanCandidates($pdo, $count);
    if (count($karyawan) === 0) {
        fail("Tidak ada karyawan aktif yang bisa dibuatkan penilaian.");
    }

    $trainingCount = min($trainingCount, count($karyawan));
    $labels = array_keys(LABEL_PATTERNS);
    $created = 0;
    $updated = 0;
    $skipped = 0;
    $trainingAssigned = 0;

    $pdo->beginTransaction();

    try {
        foreach ($karyawan as $index => $item) {
            $existing = getExistingPenilaian($pdo, (int) $periode['id'], (int) $item['id']);
            if ($existing && !$overwrite) {
                $skipped++;
                continue;
            }

            $label = pickLabelWeighted();
            $nilaiKriteria = buildNilaiKriteria($kriteria, $label);
            $totalNilai = calculateAverage($nilaiKriteria);
            $isTraining = $index < $trainingCount ? 1 : 0;
            $catatan = buildCatatan($label, $isTraining === 1);

            if ($existing) {
                $stmt = $pdo->prepare(
                    "UPDATE penilaian
                     SET penilai_id = :penilai_id,
                         nilai_kriteria = :nilai_kriteria,
                         total_nilai = :total_nilai,
                         klasifikasi = :klasifikasi,
                         catatan = :catatan,
                         status = 'selesai',
                         is_training = :is_training,
                         updated_at = NOW()
                     WHERE id = :id"
                );
                $stmt->execute([
                    ':penilai_id' => $item['penilai_id'],
                    ':nilai_kriteria' => json_encode($nilaiKriteria, JSON_UNESCAPED_UNICODE),
                    ':total_nilai' => $totalNilai,
                    ':klasifikasi' => $label,
                    ':catatan' => $catatan,
                    ':is_training' => $isTraining,
                    ':id' => $existing['id'],
                ]);
                $updated++;
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO penilaian
                        (periode_id, karyawan_id, penilai_id, nilai_kriteria, total_nilai, klasifikasi, catatan, status, is_training, created_at, updated_at)
                     VALUES
                        (:periode_id, :karyawan_id, :penilai_id, :nilai_kriteria, :total_nilai, :klasifikasi, :catatan, 'selesai', :is_training, NOW(), NOW())"
                );
                $stmt->execute([
                    ':periode_id' => $periode['id'],
                    ':karyawan_id' => $item['id'],
                    ':penilai_id' => $item['penilai_id'],
                    ':nilai_kriteria' => json_encode($nilaiKriteria, JSON_UNESCAPED_UNICODE),
                    ':total_nilai' => $totalNilai,
                    ':klasifikasi' => $label,
                    ':catatan' => $catatan,
                    ':is_training' => $isTraining,
                ]);
                $created++;
            }
        }

        // Tetapkan training secara random agar tidak bias pada nilai tertinggi.
        $trainingAssigned = normalizeTrainingFlags($pdo, (int) $periode['id'], array_column($karyawan, 'id'), $trainingCount);
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        fail('Gagal membuat data seed: ' . $e->getMessage());
    }

    echo "Seed penilaian selesai.\n";
    echo "Periode         : {$periode['nama_periode']} (#{$periode['id']})\n";
    echo "Karyawan target : " . count($karyawan) . "\n";
    echo "Dibuat          : {$created}\n";
    echo "Diupdate        : {$updated}\n";
    echo "Dilewati        : {$skipped}\n";
    echo "Training aktif  : {$trainingAssigned}\n";
    echo "Seed random     : {$seed}\n\n";

    echo "Contoh pakai:\n";
    echo "php scripts/seed_penilaian.php --count=8 --training-count=4\n";
    echo "php scripts/seed_penilaian.php --periode-id={$periode['id']} --count=5 --training-count=3 --overwrite\n";
}

function printHelp(): void
{
    echo "Seeder riwayat penilaian, data training, dan periode penilaian.\n\n";
    echo "Periode semester akan dibuat otomatis sampai tahun berjalan, tanpa melewati tanggal eksekusi script.\n\n";
    echo "Opsi:\n";
    echo "  --list-periods          Tampilkan daftar periode.\n";
    echo "  --periode-id=ID         Gunakan periode tertentu. Default: periode aktif atau periode terbaru.\n";
    echo "  --start-year=YYYY       Seed semua periode (semester) mulai tahun ini, dan semua karyawan akan dinilai.\n";
    echo "  --count=N               Jumlah karyawan target. Default: 8.\n";
    echo "  --training-count=N      Jumlah data yang ditandai training. Default: 4.\n";
    echo "  --training-percent=N    Persentase data training (0-100). Default: 80 (hanya relevan saat --start-year).\n";
    echo "  --seed=N                Seed random agar hasil konsisten. Default: 20260420.\n";
    echo "  --overwrite             Update nilai penilaian yang sudah ada.\n";
    echo "  --help                  Tampilkan bantuan.\n";
}

function loadEnv(string $path): array
{
    if (!is_file($path)) {
        fail("File .env tidak ditemukan di {$path}");
    }

    $data = parse_ini_file($path, false, INI_SCANNER_RAW);
    if ($data === false) {
        fail('Gagal membaca file .env');
    }

    foreach (['DB_NAME', 'DB_USER', 'DB_PASS'] as $key) {
        if (!array_key_exists($key, $data)) {
            fail("Konfigurasi {$key} tidak ditemukan di .env");
        }
        $data[$key] = trim((string) $data[$key], " \t\n\r\0\x0B'\"");
    }

    return $data;
}

function connectDatabase(array $config): PDO
{
    $dsn = sprintf('mysql:host=localhost;dbname=%s;charset=utf8mb4', $config['DB_NAME']);
    $pdo = new PDO($dsn, $config['DB_USER'], $config['DB_PASS'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}

function listPeriods(PDO $pdo): void
{
    $rows = $pdo->query(
        "SELECT id, nama_periode, tanggal_mulai, tanggal_selesai, status
         FROM periode_penilaian
         ORDER BY tanggal_mulai DESC, id DESC"
    )->fetchAll();

    if (!$rows) {
        echo "Belum ada periode penilaian.\n";
        return;
    }

    foreach ($rows as $row) {
        echo sprintf(
            "#%d | %s | %s s/d %s | %s\n",
            $row['id'],
            $row['nama_periode'],
            $row['tanggal_mulai'],
            $row['tanggal_selesai'],
            strtoupper((string) $row['status'])
        );
    }
}

function ensurePeriodePenilaian(PDO $pdo, int $currentYear, ?int $startYear): void
{
    $stmt = $pdo->query(
        "SELECT id, nama_periode, tanggal_mulai, tanggal_selesai, status
         FROM periode_penilaian
         ORDER BY tanggal_mulai ASC, id ASC"
    );
    $existing = $stmt->fetchAll();
    $existingMap = [];

    foreach ($existing as $row) {
        $existingMap[buildPeriodeKey($row['tanggal_mulai'], $row['tanggal_selesai'])] = $row;
    }

    $currentMonth = (int) date('n');
    $currentSemester = $currentMonth <= 6 ? 1 : 2;
    $created = 0;

    $start = detectStartYear($existing, $startYear);
    for ($year = $start; $year <= $currentYear; $year++) {
        $semesterLimit = $year < $currentYear ? 2 : $currentSemester;

        for ($semester = 1; $semester <= $semesterLimit; $semester++) {
            [$mulai, $selesai] = getSemesterRange($year, $semester);
            $key = buildPeriodeKey($mulai, $selesai);

            if (isset($existingMap[$key])) {
                continue;
            }

            $status = resolveSemesterStatus($year, $semester, $currentYear, $currentSemester);
            $stmtInsert = $pdo->prepare(
                "INSERT INTO periode_penilaian
                    (nama_periode, tanggal_mulai, tanggal_selesai, status, keterangan, created_at, updated_at)
                 VALUES
                    (:nama_periode, :tanggal_mulai, :tanggal_selesai, :status, :keterangan, NOW(), NOW())"
            );
            $stmtInsert->execute([
                ':nama_periode' => sprintf('Semester %d - %d', $semester, $year),
                ':tanggal_mulai' => $mulai,
                ':tanggal_selesai' => $selesai,
                ':status' => $status,
                ':keterangan' => 'Dibuat otomatis oleh seed_penilaian.php',
            ]);
            $created++;
        }
    }

    if ($created > 0) {
        normalizePeriodeStatus($pdo, $currentYear, $currentSemester);
    }
}

function detectStartYear(array $existing, ?int $forcedStartYear): int
{
    if ($forcedStartYear !== null) {
        return max(1900, $forcedStartYear);
    }

    if (!$existing) {
        return 2024;
    }

    $first = reset($existing);
    $year = isset($first['tanggal_mulai']) ? (int) date('Y', strtotime((string) $first['tanggal_mulai'])) : 2024;
    return max(2024, $year);
}

function getSemesterRange(int $year, int $semester): array
{
    if ($semester === 1) {
        return ["{$year}-01-01", "{$year}-06-30"];
    }

    return ["{$year}-07-01", "{$year}-12-31"];
}

function buildPeriodeKey(string $tanggalMulai, string $tanggalSelesai): string
{
    return $tanggalMulai . '|' . $tanggalSelesai;
}

function resolveSemesterStatus(int $year, int $semester, int $currentYear, int $currentSemester): string
{
    if ($year === $currentYear && $semester === $currentSemester) {
        return 'aktif';
    }

    if ($year < $currentYear || ($year === $currentYear && $semester < $currentSemester)) {
        return 'selesai';
    }

    return 'draft';
}

function normalizePeriodeStatus(PDO $pdo, int $currentYear, int $currentSemester): void
{
    $stmt = $pdo->query(
        "SELECT id, tanggal_mulai, tanggal_selesai
         FROM periode_penilaian
         ORDER BY tanggal_mulai ASC, id ASC"
    );
    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        $year = (int) date('Y', strtotime((string) $row['tanggal_mulai']));
        $semester = ((int) date('n', strtotime((string) $row['tanggal_mulai'])) <= 6) ? 1 : 2;
        $status = resolveSemesterStatus($year, $semester, $currentYear, $currentSemester);

        $updateStmt = $pdo->prepare(
            "UPDATE periode_penilaian
             SET status = :status, updated_at = NOW()
             WHERE id = :id"
        );
        $updateStmt->execute([
            ':status' => $status,
            ':id' => $row['id'],
        ]);
    }
}

function resolvePeriode(PDO $pdo, ?int $periodeId): array
{
    if ($periodeId) {
        $stmt = $pdo->prepare("SELECT id, nama_periode, status FROM periode_penilaian WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $periodeId]);
        $row = $stmt->fetch();
        if (!$row) {
            fail("Periode dengan ID {$periodeId} tidak ditemukan.");
        }
        return $row;
    }

    $row = $pdo->query(
        "SELECT id, nama_periode, status
         FROM periode_penilaian
         ORDER BY CASE WHEN status = 'aktif' THEN 0 ELSE 1 END, tanggal_mulai DESC, id DESC
         LIMIT 1"
    )->fetch();

    if (!$row) {
        fail('Tidak ada data periode penilaian.');
    }

    return $row;
}

function getKriteria(PDO $pdo): array
{
    return $pdo->query(
        "SELECT id, kode, nama, bobot
         FROM kriteria
         WHERE status = 'aktif'
         ORDER BY urutan ASC, id ASC"
    )->fetchAll();
}

function getKaryawanCandidates(PDO $pdo, int $limit): array
{
    $fallbackPenilai = $pdo->query(
        "SELECT id
         FROM karyawan
         WHERE status = 'aktif' AND role IN ('atasan', 'admin', 'superadmin')
         ORDER BY FIELD(role, 'atasan', 'admin', 'superadmin'), id ASC
         LIMIT 1"
    )->fetch();

    $fallbackId = $fallbackPenilai['id'] ?? null;

    $stmt = $pdo->prepare(
        "SELECT k.id, k.nama, k.jabatan, COALESCE(r.id_atasan, :fallback_id) AS penilai_id
         FROM karyawan k
         LEFT JOIN relasi_atasan r ON r.id_karyawan = k.id
         WHERE k.status = 'aktif'
           AND k.role NOT IN ('admin', 'superadmin')
         ORDER BY k.id ASC
         LIMIT {$limit}"
    );
    $stmt->execute([':fallback_id' => $fallbackId]);

    return $stmt->fetchAll();
}

function getAllKaryawanCandidates(PDO $pdo): array
{
    $fallbackPenilai = $pdo->query(
        "SELECT id
         FROM karyawan
         WHERE status = 'aktif' AND role IN ('atasan', 'admin', 'superadmin')
         ORDER BY FIELD(role, 'atasan', 'admin', 'superadmin'), id ASC
         LIMIT 1"
    )->fetch();

    $fallbackId = $fallbackPenilai['id'] ?? null;

    $stmt = $pdo->prepare(
        "SELECT k.id, k.nama, k.jabatan, COALESCE(r.id_atasan, :fallback_id) AS penilai_id
         FROM karyawan k
         LEFT JOIN relasi_atasan r ON r.id_karyawan = k.id
         WHERE k.status = 'aktif'
           AND k.role NOT IN ('admin', 'superadmin')
         ORDER BY k.id ASC"
    );
    $stmt->execute([':fallback_id' => $fallbackId]);

    return $stmt->fetchAll();
}

function getPeriodeFromYear(PDO $pdo, int $startYear): array
{
    // Hanya ambil periode yang sudah dibuat (sampai semester berjalan).
    $stmt = $pdo->prepare(
        "SELECT id, nama_periode, tanggal_mulai, tanggal_selesai, status
         FROM periode_penilaian
         WHERE YEAR(tanggal_mulai) >= :start_year
         ORDER BY tanggal_mulai ASC, id ASC"
    );
    $stmt->execute([':start_year' => $startYear]);
    return $stmt->fetchAll();
}

function getExistingPenilaianFull(PDO $pdo, int $periodeId, int $karyawanId): ?array
{
    $stmt = $pdo->prepare(
        "SELECT id, nilai_kriteria, total_nilai, klasifikasi, is_training
         FROM penilaian
         WHERE periode_id = :periode_id AND karyawan_id = :karyawan_id
         LIMIT 1"
    );
    $stmt->execute([
        ':periode_id' => $periodeId,
        ':karyawan_id' => $karyawanId,
    ]);

    $row = $stmt->fetch();
    return $row ?: null;
}

function getExistingPenilaian(PDO $pdo, int $periodeId, int $karyawanId): ?array
{
    $stmt = $pdo->prepare(
        "SELECT id, is_training
         FROM penilaian
         WHERE periode_id = :periode_id AND karyawan_id = :karyawan_id
         LIMIT 1"
    );
    $stmt->execute([
        ':periode_id' => $periodeId,
        ':karyawan_id' => $karyawanId,
    ]);

    $row = $stmt->fetch();
    return $row ?: null;
}

function pickLabelWeighted(): string
{
    // Distribusi ringan: mayoritas Baik/Cukup, sedikit Sangat Baik/Kurang.
    $roll = mt_rand(1, 100);
    if ($roll <= 10) {
        return 'Sangat Baik';
    }
    if ($roll <= 60) {
        return 'Baik';
    }
    if ($roll <= 90) {
        return 'Cukup';
    }
    return 'Kurang';
}

function labelFromAverage(float $avg): string
{
    if ($avg >= 3.5) {
        return 'Sangat Baik';
    }
    if ($avg >= 2.75) {
        return 'Baik';
    }
    if ($avg >= 2.0) {
        return 'Cukup';
    }
    return 'Kurang';
}

function mutateNilaiKriteriaSmooth(array $previous, array $kriteria): array
{
    // previous: list of ['kriteria_id' => int, 'nilai' => int]
    // Bikin map biar stabil walau urutan berubah.
    $prevMap = [];
    foreach ($previous as $row) {
        if (isset($row['kriteria_id'], $row['nilai'])) {
            $prevMap[(int) $row['kriteria_id']] = (int) $row['nilai'];
        }
    }

    $values = [];
    foreach ($kriteria as $item) {
        $kid = (int) $item['id'];
        $old = $prevMap[$kid] ?? mt_rand(2, 3);

        // 70% tetap, 15% turun 1, 15% naik 1 (supaya tidak lompat).
        $r = mt_rand(1, 100);
        $delta = 0;
        if ($r <= 15) {
            $delta = -1;
        } elseif ($r > 85) {
            $delta = 1;
        }

        $nilai = max(1, min(4, $old + $delta));
        $values[] = [
            'kriteria_id' => $kid,
            'nilai' => $nilai,
        ];
    }

    // Guard: kalau rata-rata terlalu jauh dari sebelumnya, tarik balik sedikit.
    $prevAvg = calculateAverage($previous);
    $avg = calculateAverage($values);
    $guard = 0;
    while ($guard < 50 && abs($avg - $prevAvg) > 0.6) {
        $direction = ($avg > $prevAvg) ? -1 : 1;
        foreach ($values as &$row) {
            if ($direction > 0 && $row['nilai'] < 4) {
                $row['nilai']++;
                break;
            }
            if ($direction < 0 && $row['nilai'] > 1) {
                $row['nilai']--;
                break;
            }
        }
        unset($row);
        $avg = calculateAverage($values);
        $guard++;
    }

    return $values;
}

function buildNilaiKriteria(array $kriteria, string $label): array
{
    $pattern = LABEL_PATTERNS[$label] ?? LABEL_PATTERNS['Baik'];
    $values = [];

    foreach ($kriteria as $index => $item) {
        $base = $pattern[$index % count($pattern)];
        $delta = mt_rand(-1, 1);
        $nilai = max(1, min(4, $base + $delta));
        $values[] = [
            'kriteria_id' => (int) $item['id'],
            'nilai' => $nilai,
        ];
    }

    return stabilizeNilaiByLabel($values, $label);
}

function stabilizeNilaiByLabel(array $nilaiKriteria, string $label): array
{
    $guard = 0;
    while ($guard < 100) {
        $avg = calculateAverage($nilaiKriteria);
        if (averageMatchesLabel($avg, $label)) {
            break;
        }

        $direction = adjustmentDirection($avg, $label);
        foreach ($nilaiKriteria as &$item) {
            if ($direction > 0 && $item['nilai'] < 4) {
                $item['nilai']++;
                break;
            }
            if ($direction < 0 && $item['nilai'] > 1) {
                $item['nilai']--;
                break;
            }
        }
        unset($item);
        $guard++;
    }

    return $nilaiKriteria;
}

function averageMatchesLabel(float $avg, string $label): bool
{
    return match ($label) {
        'Sangat Baik' => $avg >= 3.5,
        'Baik' => $avg >= 2.75 && $avg <= 3.49,
        'Cukup' => $avg >= 2.0 && $avg <= 2.74,
        'Kurang' => $avg <= 1.99,
        default => false,
    };
}

function adjustmentDirection(float $avg, string $label): int
{
    return match ($label) {
        'Sangat Baik' => ($avg < 3.5 ? 1 : 0),
        'Baik' => ($avg < 2.75 ? 1 : ($avg > 3.49 ? -1 : 0)),
        'Cukup' => ($avg < 2.0 ? 1 : ($avg > 2.74 ? -1 : 0)),
        'Kurang' => ($avg > 1.99 ? -1 : 0),
        default => 0,
    };
}

function calculateAverage(array $nilaiKriteria): float
{
    $values = array_map(static fn(array $item): float => (float) $item['nilai'], $nilaiKriteria);
    if (count($values) === 0) {
        return 0.0;
    }

    return round(array_sum($values) / count($values), 2);
}

function buildCatatan(string $label, bool $isTraining): string
{
    $notes = [
        'Sangat Baik' => 'Data seed: performa sangat kuat dan konsisten.',
        'Baik' => 'Data seed: performa baik dan memenuhi target kerja.',
        'Cukup' => 'Data seed: performa cukup, masih ada ruang peningkatan.',
        'Kurang' => 'Data seed: performa perlu pembinaan lebih lanjut.',
    ];

    // is_training bisa ditetapkan ulang secara random setelah insert/update,
    // jadi catatan jangan mengunci status training/riwayat biar tidak misleading.
    return $notes[$label] . ' Dibuat otomatis oleh seed_penilaian.php.';
}

function normalizeTrainingFlags(PDO $pdo, int $periodeId, array $karyawanIds, int $trainingCount): int
{
    if ($trainingCount <= 0 || count($karyawanIds) === 0) {
        return 0;
    }

    $placeholders = implode(',', array_fill(0, count($karyawanIds), '?'));
    $sql = "SELECT id
            FROM penilaian
            WHERE periode_id = ? AND karyawan_id IN ({$placeholders})
            ORDER BY id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge([$periodeId], array_map('intval', $karyawanIds)));
    $rows = $stmt->fetchAll();

    if (!$rows) {
        return 0;
    }

    $allIds = array_column($rows, 'id');
    shuffleArray($allIds);
    $trainingIds = array_slice($allIds, 0, min($trainingCount, count($allIds)));

    $resetSql = "UPDATE penilaian SET is_training = 0, updated_at = NOW() WHERE id IN (" . implode(',', array_fill(0, count($allIds), '?')) . ")";
    $resetStmt = $pdo->prepare($resetSql);
    $resetStmt->execute(array_map('intval', $allIds));

    if ($trainingIds) {
        $trainingSql = "UPDATE penilaian SET is_training = 1, updated_at = NOW() WHERE id IN (" . implode(',', array_fill(0, count($trainingIds), '?')) . ")";
        $trainingStmt = $pdo->prepare($trainingSql);
        $trainingStmt->execute(array_map('intval', $trainingIds));
    }

    return count($trainingIds);
}

function shuffleArray(array &$values): void
{
    // Fisher-Yates shuffle, deterministic karena mt_srand() dipanggil di awal.
    $n = count($values);
    for ($i = $n - 1; $i > 0; $i--) {
        $j = mt_rand(0, $i);
        if ($i !== $j) {
            $tmp = $values[$i];
            $values[$i] = $values[$j];
            $values[$j] = $tmp;
        }
    }
}

function assignTrainingByPercent(PDO $pdo, int $periodeId, array $karyawanIds, int $percent): int
{
    if ($percent <= 0 || count($karyawanIds) === 0) {
        return 0;
    }

    $percent = max(0, min(100, $percent));
    $placeholders = implode(',', array_fill(0, count($karyawanIds), '?'));
    $sql = "SELECT id
            FROM penilaian
            WHERE periode_id = ? AND karyawan_id IN ({$placeholders})
            ORDER BY id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge([$periodeId], array_map('intval', $karyawanIds)));
    $rows = $stmt->fetchAll();
    if (!$rows) {
        return 0;
    }

    $allIds = array_column($rows, 'id');
    shuffleArray($allIds);

    $trainingCount = (int) floor(count($allIds) * ($percent / 100));
    $trainingIds = array_slice($allIds, 0, $trainingCount);

    $resetSql = "UPDATE penilaian SET is_training = 0, updated_at = NOW() WHERE id IN (" . implode(',', array_fill(0, count($allIds), '?')) . ")";
    $resetStmt = $pdo->prepare($resetSql);
    $resetStmt->execute(array_map('intval', $allIds));

    if ($trainingIds) {
        $trainingSql = "UPDATE penilaian SET is_training = 1, updated_at = NOW() WHERE id IN (" . implode(',', array_fill(0, count($trainingIds), '?')) . ")";
        $trainingStmt = $pdo->prepare($trainingSql);
        $trainingStmt->execute(array_map('intval', $trainingIds));
    }

    return count($trainingIds);
}

function seedAllPeriods(PDO $pdo, array $kriteria, int $startYear, int $trainingPercent, bool $overwrite): void
{
    $periods = getPeriodeFromYear($pdo, $startYear);
    if (!$periods) {
        fail("Tidak ada periode untuk start-year={$startYear}. Jalankan ensurePeriodePenilaian atau cek tabel periode_penilaian.");
    }

    $karyawan = getAllKaryawanCandidates($pdo);
    if (!$karyawan) {
        fail("Tidak ada karyawan aktif yang bisa dibuatkan penilaian.");
    }

    $karyawanIds = array_map(static fn(array $r): int => (int) $r['id'], $karyawan);

    // State nilai per karyawan untuk menjaga transisi halus antar semester.
    $state = []; // karyawan_id => nilai_kriteria[]

    foreach ($periods as $periode) {
        $periodeId = (int) $periode['id'];

        $created = 0;
        $updated = 0;
        $skipped = 0;

        $pdo->beginTransaction();
        try {
            foreach ($karyawan as $emp) {
                $karyawanId = (int) $emp['id'];
                $existing = getExistingPenilaianFull($pdo, $periodeId, $karyawanId);

                if ($existing && !$overwrite) {
                    $skipped++;
                    // Pakai data existing sebagai "prev state" agar periode berikutnya tetap smooth.
                    $decoded = json_decode((string) $existing['nilai_kriteria'], true);
                    if (is_array($decoded)) {
                        $state[$karyawanId] = $decoded;
                    }
                    continue;
                }

                if ($existing) {
                    // Kalau record ada, transisi smooth berbasis state (atau existing kalau state belum ada).
                    $prev = $state[$karyawanId] ?? null;
                    if (!$prev) {
                        $decoded = json_decode((string) $existing['nilai_kriteria'], true);
                        $prev = is_array($decoded) ? $decoded : null;
                    }
                    if (!$prev) {
                        $prev = buildNilaiKriteria($kriteria, pickLabelWeighted());
                    }
                    $nilaiKriteria = mutateNilaiKriteriaSmooth($prev, $kriteria);
                } else {
                    // Record baru: kalau belum ada state, buat baseline; kalau ada, mutasi ringan.
                    if (!isset($state[$karyawanId])) {
                        $nilaiKriteria = buildNilaiKriteria($kriteria, pickLabelWeighted());
                    } else {
                        $nilaiKriteria = mutateNilaiKriteriaSmooth($state[$karyawanId], $kriteria);
                    }
                }

                $state[$karyawanId] = $nilaiKriteria;
                $totalNilai = calculateAverage($nilaiKriteria);
                $label = labelFromAverage($totalNilai);
                $catatan = buildCatatan($label, false);

                if ($existing) {
                    $stmt = $pdo->prepare(
                        "UPDATE penilaian
                         SET penilai_id = :penilai_id,
                             nilai_kriteria = :nilai_kriteria,
                             total_nilai = :total_nilai,
                             klasifikasi = :klasifikasi,
                             catatan = :catatan,
                             status = 'selesai',
                             updated_at = NOW()
                         WHERE id = :id"
                    );
                    $stmt->execute([
                        ':penilai_id' => $emp['penilai_id'],
                        ':nilai_kriteria' => json_encode($nilaiKriteria, JSON_UNESCAPED_UNICODE),
                        ':total_nilai' => $totalNilai,
                        ':klasifikasi' => $label,
                        ':catatan' => $catatan,
                        ':id' => $existing['id'],
                    ]);
                    $updated++;
                } else {
                    $stmt = $pdo->prepare(
                        "INSERT INTO penilaian
                            (periode_id, karyawan_id, penilai_id, nilai_kriteria, total_nilai, klasifikasi, catatan, status, is_training, created_at, updated_at)
                         VALUES
                            (:periode_id, :karyawan_id, :penilai_id, :nilai_kriteria, :total_nilai, :klasifikasi, :catatan, 'selesai', 0, NOW(), NOW())"
                    );
                    $stmt->execute([
                        ':periode_id' => $periodeId,
                        ':karyawan_id' => $karyawanId,
                        ':penilai_id' => $emp['penilai_id'],
                        ':nilai_kriteria' => json_encode($nilaiKriteria, JSON_UNESCAPED_UNICODE),
                        ':total_nilai' => $totalNilai,
                        ':klasifikasi' => $label,
                        ':catatan' => $catatan,
                    ]);
                    $created++;
                }
            }

            // Setelah seluruh karyawan dinilai di periode ini, tetapkan training secara random (default 80%).
            assignTrainingByPercent($pdo, $periodeId, $karyawanIds, $trainingPercent);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            fail('Gagal seed multi-periode: ' . $e->getMessage());
        }

        echo sprintf(
            "Periode %s (#%d): dibuat=%d, update=%d, skip=%d\n",
            (string) $periode['nama_periode'],
            $periodeId,
            $created,
            $updated,
            $skipped
        );
    }
}

function fail(string $message): void
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}
