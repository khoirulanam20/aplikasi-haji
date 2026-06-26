<?php

namespace App\Http\Requests\Superadmin;

use App\Support\Wilayah\KemendagriWilayahRepository;
use Illuminate\Validation\Validator;

trait ValidatesHajjWilayah
{
    protected function mergeWilayahNames(array $payload): array
    {
        $repo = app(KemendagriWilayahRepository::class);
        $kecamatanKode = $payload['kecamatan_kode'] ?? null;
        $desaKode = $payload['desa_kode'] ?? null;

        if ($kecamatanKode) {
            $payload['kecamatan'] = $repo->kecamatanNama($kecamatanKode);
        } else {
            $payload['kecamatan'] = null;
            $payload['desa'] = null;
        }

        if ($kecamatanKode && $desaKode) {
            $payload['desa'] = $repo->desaNama($kecamatanKode, $desaKode);
        } elseif ($kecamatanKode) {
            $payload['desa'] = null;
        }

        unset($payload['kecamatan_kode'], $payload['desa_kode']);

        return $payload;
    }

    protected function withWilayahValidation(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $repo = app(KemendagriWilayahRepository::class);
            $kecamatanKode = $this->input('kecamatan_kode');
            $desaKode = $this->input('desa_kode');

            if ($kecamatanKode && ! $repo->kecamatanNama($kecamatanKode)) {
                $validator->errors()->add('kecamatan_kode', 'Kecamatan tidak valid.');
            }

            if ($desaKode) {
                if (! $kecamatanKode) {
                    $validator->errors()->add('desa_kode', 'Pilih kecamatan terlebih dahulu.');
                } elseif (! $repo->desaNama($kecamatanKode, $desaKode)) {
                    $validator->errors()->add('desa_kode', 'Desa/kelurahan tidak valid.');
                }
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    protected function wilayahRules(): array
    {
        return [
            'kecamatan_kode' => ['nullable', 'string', 'max:20'],
            'desa_kode' => ['nullable', 'string', 'max:20'],
            'desa' => ['nullable', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:255'],
        ];
    }
}
