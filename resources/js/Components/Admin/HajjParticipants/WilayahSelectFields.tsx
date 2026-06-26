import { useMemo } from 'react';
import InputError from '@/Components/InputError';
import { Label } from '@/Components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { WilayahOptions } from '@/types';

interface WilayahSelectFieldsProps {
    wilayah: WilayahOptions;
    kecamatanKode: string;
    desaKode: string;
    onKecamatanChange: (kode: string) => void;
    onDesaChange: (kode: string) => void;
    errors?: {
        kecamatan_kode?: string;
        desa_kode?: string;
    };
}

export function findKecamatanKode(wilayah: WilayahOptions, nama: string | null | undefined): string {
    if (!nama) {
        return '';
    }

    return (
        wilayah.kecamatan.find((item) => item.nama.localeCompare(nama, 'id', { sensitivity: 'base' }) === 0)
            ?.kode ?? ''
    );
}

export function findDesaKode(
    wilayah: WilayahOptions,
    kecamatanKode: string,
    nama: string | null | undefined,
): string {
    if (!kecamatanKode || !nama) {
        return '';
    }

    const kecamatan = wilayah.kecamatan.find((item) => item.kode === kecamatanKode);
    if (!kecamatan) {
        return '';
    }

    return (
        kecamatan.desa.find((item) => item.nama.localeCompare(nama, 'id', { sensitivity: 'base' }) === 0)
            ?.kode ?? ''
    );
}

export default function WilayahSelectFields({
    wilayah,
    kecamatanKode,
    desaKode,
    onKecamatanChange,
    onDesaChange,
    errors,
}: WilayahSelectFieldsProps) {
    const desaOptions = useMemo(() => {
        return wilayah.kecamatan.find((item) => item.kode === kecamatanKode)?.desa ?? [];
    }, [wilayah, kecamatanKode]);

    return (
        <>
            <div className="grid gap-4 sm:grid-cols-2">
                <div>
                    <Label>Provinsi</Label>
                    <Select value={wilayah.provinsi.kode} disabled>
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={wilayah.provinsi.kode}>{wilayah.provinsi.nama}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div>
                    <Label>Kabupaten/Kota</Label>
                    <Select value={wilayah.kabupaten.kode} disabled>
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={wilayah.kabupaten.kode}>{wilayah.kabupaten.nama}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
                <div>
                    <Label>Kecamatan</Label>
                    <Select
                        value={kecamatanKode || undefined}
                        onValueChange={(value) => {
                            onKecamatanChange(value);
                            onDesaChange('');
                        }}
                    >
                        <SelectTrigger>
                            <SelectValue placeholder="Pilih kecamatan" />
                        </SelectTrigger>
                        <SelectContent>
                            {wilayah.kecamatan.map((item) => (
                                <SelectItem key={item.kode} value={item.kode}>
                                    {item.nama}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <InputError message={errors?.kecamatan_kode} />
                </div>
                <div>
                    <Label>Desa/Kelurahan</Label>
                    <Select
                        value={desaKode || undefined}
                        onValueChange={onDesaChange}
                        disabled={!kecamatanKode}
                    >
                        <SelectTrigger>
                            <SelectValue placeholder={kecamatanKode ? 'Pilih desa/kelurahan' : 'Pilih kecamatan dulu'} />
                        </SelectTrigger>
                        <SelectContent>
                            {desaOptions.map((item) => (
                                <SelectItem key={item.kode} value={item.kode}>
                                    {item.nama}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <InputError message={errors?.desa_kode} />
                </div>
            </div>
        </>
    );
}
