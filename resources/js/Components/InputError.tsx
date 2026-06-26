interface InputErrorProps {
    message?: string;
    className?: string;
}

export default function InputError({ message, className = '' }: InputErrorProps) {
    if (!message) {
        return null;
    }

    return <p className={`text-sm text-danger ${className}`}>{message}</p>;
}
