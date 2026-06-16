<?php

namespace app;

class KnnJsonStorageService
{
    protected string $relativePath = 'storage/app/knn/hasil_analisa.json';

    public function getFilePath(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $this->relativePath);
    }

    public function ensureStorageReady(): void
    {
        $directory = dirname($this->getFilePath());
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        if (!file_exists($this->getFilePath())) {
            file_put_contents($this->getFilePath(), json_encode($this->getEmptyStore(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    public function hasAnalyses(): bool
    {
        $store = $this->readStore();
        return !empty($store['analyses']);
    }

    public function saveAnalysis(array $analysis): array
    {
        $store = $this->readStore();

        $analysis['id'] = (string) ($analysis['id'] ?? $this->generateAnalysisId());
        $analysis['created_at'] = $analysis['created_at'] ?? date('Y-m-d H:i:s');
        $analysis['updated_at'] = date('Y-m-d H:i:s');

        $analyses = $store['analyses'] ?? [];
        $replaced = false;

        foreach ($analyses as $index => $item) {
            if (($item['id'] ?? null) === $analysis['id']) {
                $analyses[$index] = $analysis;
                $replaced = true;
                break;
            }
        }

        if (!$replaced) {
            array_unshift($analyses, $analysis);
        }

        usort($analyses, function ($a, $b) {
            return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
        });

        $store['analyses'] = array_values($analyses);
        $store['current_analysis_id'] = $analysis['id'];

        $this->writeStore($store);

        return $analysis;
    }

    public function getAnalysis(?string $analysisId = null): ?array
    {
        $store = $this->readStore();
        $analysisId = $analysisId ?: ($store['current_analysis_id'] ?? null);

        if (empty($analysisId)) {
            return $store['analyses'][0] ?? null;
        }

        foreach ($store['analyses'] as $analysis) {
            if (($analysis['id'] ?? null) === $analysisId) {
                return $analysis;
            }
        }

        return null;
    }

    public function getLatestAnalysis(?int $periodeId = null): ?array
    {
        $store = $this->readStore();

        foreach ($store['analyses'] as $analysis) {
            if ($periodeId === null || intval($analysis['params']['periode_id'] ?? 0) === $periodeId) {
                return $analysis;
            }
        }

        return null;
    }

    public function listAnalyses(int $limit = 20, int $offset = 0): array
    {
        $store = $this->readStore();
        $analyses = array_slice($store['analyses'] ?? [], $offset, $limit);

        return [
            'data' => array_map(fn($item) => $this->toSummary($item), $analyses),
            'total' => count($store['analyses'] ?? []),
            'limit' => $limit,
            'offset' => $offset,
        ];
    }

    public function deleteAnalysis(string $analysisId): bool
    {
        $store = $this->readStore();
        $remaining = array_values(array_filter($store['analyses'] ?? [], function ($item) use ($analysisId) {
            return ($item['id'] ?? null) !== $analysisId;
        }));

        if (count($remaining) === count($store['analyses'] ?? [])) {
            return false;
        }

        $store['analyses'] = $remaining;
        $store['current_analysis_id'] = $remaining[0]['id'] ?? null;
        $this->writeStore($store);

        return true;
    }

    public function findResult(string $resultId): ?array
    {
        $store = $this->readStore();

        foreach ($store['analyses'] as $analysis) {
            foreach (($analysis['hasil'] ?? []) as $result) {
                if (($result['result_id'] ?? null) === $resultId) {
                    return [
                        'analysis' => $analysis,
                        'result' => $result,
                    ];
                }
            }
        }

        return null;
    }

    public function buildLookup(?int $periodeId = null): array
    {
        $lookup = [];
        $store = $this->readStore();

        foreach ($store['analyses'] as $analysis) {
            $analysisPeriodeId = intval($analysis['params']['periode_id'] ?? 0);
            if ($periodeId !== null && $analysisPeriodeId !== $periodeId) {
                continue;
            }

            foreach (($analysis['hasil'] ?? []) as $result) {
                $karyawan = $result['karyawan'] ?? [];
                $key = intval($karyawan['karyawan_id'] ?? 0) . '-' . $analysisPeriodeId;
                if (!isset($lookup[$key])) {
                    $lookup[$key] = [
                        'analysis_id' => $analysis['id'] ?? null,
                        'periode_id' => $analysisPeriodeId,
                        'karyawan_id' => intval($karyawan['karyawan_id'] ?? 0),
                        'nama_karyawan' => $karyawan['nama'] ?? '',
                        'hasil_klasifikasi' => $result['hasil_klasifikasi'] ?? null,
                        'confidence' => isset($result['confidence']) ? floatval($result['confidence']) : null,
                        'result_id' => $result['result_id'] ?? null,
                        'created_at' => $analysis['created_at'] ?? null,
                    ];
                }
            }
        }

        return $lookup;
    }

    protected function toSummary(array $analysis): array
    {
        return [
            'id' => $analysis['id'] ?? null,
            'kode_analisa' => $analysis['kode_analisa'] ?? null,
            'nama_analisa' => $analysis['nama_analisa'] ?? null,
            'periode_id' => $analysis['params']['periode_id'] ?? null,
            'nilai_k' => $analysis['params']['k'] ?? null,
            'metode_jarak' => $analysis['params']['metode_jarak'] ?? null,
            'normalisasi' => $analysis['params']['normalisasi'] ?? null,
            'total_data_training' => count($analysis['data_training'] ?? []),
            'total_data_testing' => count($analysis['hasil'] ?? []),
            'status' => 'selesai',
            'created_at' => $analysis['created_at'] ?? null,
            'updated_at' => $analysis['updated_at'] ?? null,
            'dibuat_oleh' => $analysis['dibuat_oleh'] ?? null,
            'dibuat_oleh_nama' => $analysis['dibuat_oleh_nama'] ?? null,
            'total_sangat_baik' => $analysis['statistik']['total_sangat_baik'] ?? 0,
            'total_baik' => $analysis['statistik']['total_baik'] ?? 0,
            'total_cukup' => $analysis['statistik']['total_cukup'] ?? 0,
            'total_kurang' => $analysis['statistik']['total_kurang'] ?? 0,
            'waktu_proses_detik' => $analysis['waktu_proses'] ?? 0,
        ];
    }

    protected function readStore(): array
    {
        $this->ensureStorageReady();
        $content = file_get_contents($this->getFilePath());

        if ($content === false || trim($content) === '') {
            return $this->getEmptyStore();
        }

        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            return $this->getEmptyStore();
        }

        $decoded['analyses'] = array_values($decoded['analyses'] ?? []);
        $decoded['current_analysis_id'] = $decoded['current_analysis_id'] ?? null;

        return $decoded;
    }

    protected function writeStore(array $store): void
    {
        $this->ensureStorageReady();
        file_put_contents($this->getFilePath(), json_encode($store, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    protected function getEmptyStore(): array
    {
        return [
            'current_analysis_id' => null,
            'analyses' => [],
        ];
    }

    protected function generateAnalysisId(): string
    {
        return 'ANL-' . date('YmdHis') . '-' . substr(md5(uniqid('', true)), 0, 6);
    }
}
