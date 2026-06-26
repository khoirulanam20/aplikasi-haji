import * as React from 'react';
import { useState } from 'react';
import IconoirIcon from '@/Components/IconoirIcon';
import { Input, type InputProps } from '@/Components/ui/input';
import { cn } from '@/lib/utils';

const PasswordInput = React.forwardRef<HTMLInputElement, InputProps>(
    ({ className, ...props }, ref) => {
        const [visible, setVisible] = useState(false);

        return (
            <div className="relative">
                <Input
                    ref={ref}
                    type={visible ? 'text' : 'password'}
                    className={cn('pr-10', className)}
                    {...props}
                />
                <button
                    type="button"
                    className="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1 text-text-secondary transition-colors hover:text-text-primary"
                    onClick={() => setVisible((value) => !value)}
                    aria-label={visible ? 'Sembunyikan password' : 'Tampilkan password'}
                    tabIndex={-1}
                >
                    <IconoirIcon name={visible ? 'eye-closed' : 'eye'} className="text-lg" />
                </button>
            </div>
        );
    },
);
PasswordInput.displayName = 'PasswordInput';

export { PasswordInput };
