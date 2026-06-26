import * as React from 'react';
import * as CheckboxPrimitive from '@radix-ui/react-checkbox';
import IconoirIcon from '@/Components/IconoirIcon';
import { cn } from '@/lib/utils';

const Checkbox = React.forwardRef<
    React.ElementRef<typeof CheckboxPrimitive.Root>,
    React.ComponentPropsWithoutRef<typeof CheckboxPrimitive.Root>
>(({ className, ...props }, ref) => (
    <CheckboxPrimitive.Root
        ref={ref}
        className={cn(
            'peer h-4 w-4 shrink-0 rounded border border-border focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:border-primary data-[state=checked]:bg-primary data-[state=checked]:text-white',
            className,
        )}
        {...props}
    >
        <CheckboxPrimitive.Indicator className="flex items-center justify-center text-current">
            <IconoirIcon name="check" className="text-xs text-white" />
        </CheckboxPrimitive.Indicator>
    </CheckboxPrimitive.Root>
));
Checkbox.displayName = CheckboxPrimitive.Root.displayName;

export { Checkbox };
