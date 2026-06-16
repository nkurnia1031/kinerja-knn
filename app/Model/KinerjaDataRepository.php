<?php

namespace app\Model;

use app\DB;

class KinerjaDataRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = DB::con();
    }

    public function getTrainingDataset(?int $testingPeriodeId = null): array
    {
        [$whereSql, $params] = $this->buildPeriodeExclusionClause($testingPeriodeId, 'p.periode_id');

        $sql = "SELECT
                    p.id,
                    k.id AS karyawan_id,
                    k.nama,
                    k.jabatan,
                    pp.nama_periode,
                    p.nilai_kriteria,
                    p.klasifikasi,
                    p.total_nilai,
                    p.periode_id
                FROM penilaian p
                JOIN karyawan k ON p.karyawan_id = k.id
                LEFT JOIN periode_penilaian pp ON pp.id = p.periode_id
                WHERE p.is_training = 1
                  AND p.status = 'selesai'
                  AND p.klasifikasi IS NOT NULL
                  AND p.klasifikasi <> ''
                  {$whereSql}
                ORDER BY p.periode_id ASC, k.nama ASC";

        return empty($params) ? $this->db->run($sql) : $this->db->safeQuery($sql, $params);
    }

    public function getTestingDataset(?int $testingPeriodeId = null): array
    {
        [$whereSql, $params] = $this->buildPeriodeFilterClause($testingPeriodeId, 'p.periode_id');

        $sql = "SELECT
                    p.id,
                    k.id AS karyawan_id,
                    k.nama,
                    k.jabatan,
                    k.username AS nomor_pekerja,
                    k.pekerjaan,
                    p.nilai_kriteria,
                    p.total_nilai,
                    p.klasifikasi,
                    p.periode_id,
                    pp.nama_periode,
                    relasi.atasan_nama
                FROM penilaian p
                JOIN karyawan k ON p.karyawan_id = k.id
                LEFT JOIN periode_penilaian pp ON pp.id = p.periode_id
                LEFT JOIN (
                    SELECT
                        r.id_karyawan,
                        GROUP_CONCAT(DISTINCT atasan.nama ORDER BY atasan.nama SEPARATOR ', ') AS atasan_nama
                    FROM relasi_atasan r
                    JOIN karyawan atasan ON atasan.id = r.id_atasan
                    GROUP BY r.id_karyawan
                ) relasi ON relasi.id_karyawan = k.id
                WHERE p.status = 'selesai'
                  {$whereSql}
                ORDER BY pp.tanggal_mulai DESC, k.nama ASC, p.id DESC";

        return empty($params) ? $this->db->run($sql) : $this->db->safeQuery($sql, $params);
    }

    public function getTrainingStatistics(?int $activePeriodeId = null): array
    {
        [$whereSql, $params] = $this->buildPeriodeExclusionClause($activePeriodeId, 'periode_id');

        $totalTraining = ($params
            ? $this->db->safeQuery(
                "SELECT COUNT(*) AS total
                 FROM penilaian
                 WHERE is_training = 1
                   AND status = 'selesai'
                   {$whereSql}",
                $params
            )
            : $this->db->run("SELECT COUNT(*) AS total FROM penilaian WHERE is_training = 1 AND status = 'selesai'")
        )[0]->total ?? 0;

        $totalPenilaian = ($params
            ? $this->db->safeQuery(
                "SELECT COUNT(*) AS total
                 FROM penilaian
                 WHERE status = 'selesai'
                 {$whereSql}",
                $params
            )
            : $this->db->run("SELECT COUNT(*) AS total FROM penilaian WHERE status = 'selesai'")
        )[0]->total ?? 0;

        $distribusi = ($params
            ? $this->db->safeQuery(
                "SELECT klasifikasi, COUNT(*) AS total
                 FROM penilaian
                 WHERE is_training = 1
                   AND status = 'selesai'
                   AND klasifikasi IS NOT NULL
                   {$whereSql}
                 GROUP BY klasifikasi",
                $params
            )
            : $this->db->run(
                "SELECT klasifikasi, COUNT(*) AS total
                 FROM penilaian
                 WHERE is_training = 1
                   AND status = 'selesai'
                   AND klasifikasi IS NOT NULL
                 GROUP BY klasifikasi"
            )
        );

        return [
            'total_training' => $totalTraining,
            'total_penilaian' => $totalPenilaian,
            'distribusi' => $distribusi,
            'coverage' => $totalPenilaian > 0 ? round(($totalTraining / $totalPenilaian) * 100, 2) : 0,
        ];
    }

    protected function buildPeriodeExclusionClause(?int $periodeId, string $column): array
    {
        if (empty($periodeId)) {
            return ['', []];
        }

        return [" AND {$column} <> ?", [$periodeId]];
    }

    protected function buildPeriodeFilterClause(?int $periodeId, string $column): array
    {
        if (empty($periodeId)) {
            return ['', []];
        }

        return [" AND {$column} = ?", [$periodeId]];
    }
}
