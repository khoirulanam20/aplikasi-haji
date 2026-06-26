import InputError from '@/Components/InputError';
import { Checkbox } from '@/Components/ui/checkbox';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { PasswordInput } from '@/Components/ui/password-input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { Role } from '@/types';

export type UserFormData = {
    name: string;
    username: string;
    email: string;
    password: string;
    role: string;
    is_active: boolean;
};

interface UserFormFieldsProps {
    form: {
        data: UserFormData;
        setData: <K extends keyof UserFormData>(key: K, value: UserFormData[K]) => void;
        errors: Partial<Record<string, string>>;
    };
    roles: Role[];
    includePassword?: boolean;
}

export default function UserFormFields({ form, roles, includePassword = false }: UserFormFieldsProps) {
    const { data, setData, errors } = form;

    return (
        <>
            <div>
                <Label>Nama</Label>
                <Input value={data.name} onChange={(e) => setData('name', e.target.value)} required />
                <InputError message={errors.name} />
            </div>
            <div>
                <Label>Username</Label>
                <Input value={data.username} onChange={(e) => setData('username', e.target.value)} />
                <InputError message={errors.username} />
            </div>
            <div>
                <Label>Email</Label>
                <Input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} required />
                <InputError message={errors.email} />
            </div>
            {includePassword && (
                <div>
                    <Label>Password</Label>
                    <PasswordInput
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        required
                    />
                    <InputError message={errors.password} />
                </div>
            )}
            <div>
                <Label>Role</Label>
                <Select value={data.role} onValueChange={(value) => setData('role', value)}>
                    <SelectTrigger>
                        <SelectValue placeholder="Pilih role" />
                    </SelectTrigger>
                    <SelectContent>
                        {roles.map((role) => (
                            <SelectItem key={role.id} value={role.name}>
                                {role.title}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <InputError message={errors.role} />
            </div>
            <label className="flex items-center gap-2">
                <Checkbox
                    checked={data.is_active}
                    onCheckedChange={(checked) => setData('is_active', checked === true)}
                />
                <span className="text-sm">Aktif</span>
            </label>
        </>
    );
}
