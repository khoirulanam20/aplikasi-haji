<?php

namespace App\Support\Wilayah;

use Illuminate\Support\Facades\File;
use RuntimeException;

class KemendagriWilayahRepository
{
    /** @var array<string, mixed>|null */
    private static ?array $cache = null;

    /**
     * @return array{
     *     provinsi: array{kode: string, nama: string},
     *     kabupaten: array{kode: string, nama: string},
     *     kecamatan: list<array{kode: string, nama: string, desa: list<array{kode: string, nama: string}>}>
     * }
     */
    public function tree(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $path = config('kemendagri.data_path');

        if (! is_string($path) || ! File::exists($path)) {
            throw new RuntimeException('Data wilayah Kemendagri tidak ditemukan.');
        }

        $data = json_decode(File::get($path), true);

        if (! is_array($data) || ! isset($data['kecamatan']) || ! is_array($data['kecamatan'])) {
            throw new RuntimeException('Format data wilayah Kemendagri tidak valid.');
        }

        self::$cache = $data;

        return self::$cache;
    }

    /**
     * @return array{
     *     provinsi: array{kode: string, nama: string},
     *     kabupaten: array{kode: string, nama: string},
     *     kecamatan: list<array{kode: string, nama: string}>
     * }
     */
    public function forFrontend(): array
    {
        $tree = $this->tree();

        return [
            'provinsi' => $tree['provinsi'],
            'kabupaten' => $tree['kabupaten'],
            'kecamatan' => $tree['kecamatan'],
        ];
    }

    public function findKecamatanKodeByNama(?string $nama): ?string
    {
        if ($nama === null || $nama === '') {
            return null;
        }

        foreach ($this->tree()['kecamatan'] as $kecamatan) {
            if (strcasecmp($kecamatan['nama'], $nama) === 0) {
                return $kecamatan['kode'];
            }
        }

        return null;
    }

    public function findDesaKodeByNama(string $kecamatanKode, ?string $nama): ?string
    {
        if ($nama === null || $nama === '') {
            return null;
        }

        foreach ($this->tree()['kecamatan'] as $kecamatan) {
            if ($kecamatan['kode'] !== $kecamatanKode) {
                continue;
            }

            foreach ($kecamatan['desa'] as $desa) {
                if (strcasecmp($desa['nama'], $nama) === 0) {
                    return $desa['kode'];
                }
            }
        }

        return null;
    }

    public function kecamatanNama(string $kode): ?string
    {
        foreach ($this->tree()['kecamatan'] as $kecamatan) {
            if ($kecamatan['kode'] === $kode) {
                return $kecamatan['nama'];
            }
        }

        return null;
    }

    public function desaNama(string $kecamatanKode, string $desaKode): ?string
    {
        foreach ($this->tree()['kecamatan'] as $kecamatan) {
            if ($kecamatan['kode'] !== $kecamatanKode) {
                continue;
            }

            foreach ($kecamatan['desa'] as $desa) {
                if ($desa['kode'] === $desaKode) {
                    return $desa['nama'];
                }
            }
        }

        return null;
    }
}
