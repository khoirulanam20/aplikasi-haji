<?php

namespace App\Http\Requests\Superadmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHajjParticipantRequest extends FormRequest
{
    use ValidatesHajjWilayah;

    public function authorize(): bool
    {
        return true;
    }

    public function withValidator($validator): void
    {
        $this->withWilayahValidation($validator);
    }

    /**
     * @return array<string, mixed>|mixed
     */
    public function validated($key = null, $default = null)
    {
        $data = $this->mergeWilayahNames(parent::validated());

        if ($key === null) {
            return $data;
        }

        return data_get($data, $key, $default);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tahun_haji' => ['required', 'integer', 'min:2000', 'max:2100'],
            'nomor_porsi' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('hajj_participants')->where(fn ($q) => $q->where('tahun_haji', $this->integer('tahun_haji'))),
            ],
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'kloter' => ['nullable', 'string', 'max:20'],
            'rombongan' => ['nullable', 'string', 'max:20'],
            'regu' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            ...$this->wilayahRules(),
        ];
    }
}
