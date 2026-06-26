import * as React from 'react';
import * as DialogPrimitive from '@radix-ui/react-dialog';
import IconoirIcon from '@/Components/IconoirIcon';
import { cn } from '@/lib/utils';

const Sheet = DialogPrimitive.Root;
const SheetTrigger = DialogPrimitive.Trigger;
const SheetClose = DialogPrimitive.Close;

const SheetContent = React.forwardRef<
    React.ElementRef<typeof DialogPrimitive.Content>,
    React.ComponentPropsWithoutRef<typeof DialogPrimitive.Content>
>(({ className, children, ...props }, ref) => (
    <DialogPrimitive.Portal>
        <DialogPrimitive.Overlay className="fixed inset-0 z-50 bg-black/50" />
        <DialogPrimitive.Content
            ref={ref}
            className={cn(
                'fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-border bg-surface shadow-xl',
                className,
            )}
            {...props}
        >
            <DialogPrimitive.Title className="sr-only">Menu navigasi</DialogPrimitive.Title>
            {children}
            <DialogPrimitive.Close className="absolute right-4 top-4 rounded-sm opacity-70 hover:opacity-100">
                <IconoirIcon name="xmark" className="text-base" />
            </DialogPrimitive.Close>
        </DialogPrimitive.Content>
    </DialogPrimitive.Portal>
));
SheetContent.displayName = 'SheetContent';

export { Sheet, SheetTrigger, SheetClose, SheetContent };
