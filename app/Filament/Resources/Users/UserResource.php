<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/**
 * Staff accounts.
 *
 * Separate from customers, who live on their own guard and never reach this
 * panel. What a member of staff can actually do is decided entirely by the roles
 * assigned here — including whether they may see which carrier flew a shipment —
 * so this screen and the Roles screen are the whole of access control.
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'staff account';

    protected static ?string $pluralModelLabel = 'staff accounts';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    /**
     * Nobody may delete their own account.
     *
     * Not a permission question — a Super Admin deleting themselves mid-session
     * locks the business out of its own panel, and no amount of care prevents an
     * accidental click.
     */
    public static function canDelete(Model $record): bool
    {
        return parent::canDelete($record) && $record->getKey() !== auth()->id();
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) User::query()->count();
    }
}
