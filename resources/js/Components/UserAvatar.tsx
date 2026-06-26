import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar';
import { User } from '@/types';

interface UserAvatarProps {
    user?: Pick<User, 'name' | 'avatar_url' | 'avatar_initial'> | null;
    className?: string;
}

export default function UserAvatar({ user, className }: UserAvatarProps) {
    return (
        <Avatar className={className}>
            {user?.avatar_url && <AvatarImage src={user.avatar_url} alt={user.name} />}
            <AvatarFallback>{user?.avatar_initial ?? 'U'}</AvatarFallback>
        </Avatar>
    );
}
