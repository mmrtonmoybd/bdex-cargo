<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (User $record): string => $record->email),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->placeholder('No role — cannot sign in')
                    ->color(fn (string $state): string => $state === 'super_admin' ? 'danger' : 'gray'),
                IconColumn::make('can_see_carriers')
                    ->label('Carrier access')
                    ->boolean()
                    ->state(fn (User $record): bool => $record->can('ViewCarrierDetails'))
                    ->trueIcon('heroicon-o-eye')
                    ->trueColor('warning')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->falseColor('gray')
                    ->tooltip(fn (User $record): string => $record->can('ViewCarrierDetails')
                        ? 'May see which carrier carried a shipment'
                        : 'Cannot see carrier details'),
                IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Added')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                Filter::make('without_role')
                    ->label('Cannot sign in (no role)')
                    ->query(fn (Builder $query): Builder => $query->doesntHave('roles')),
                Filter::make('carrier_access')
                    ->label('Has carrier access')
                    ->query(fn (Builder $query): Builder => $query->whereHas(
                        'roles',
                        fn (Builder $roles) => $roles->whereHas(
                            'permissions',
                            fn (Builder $permissions) => $permissions->where('name', 'ViewCarrierDetails')
                        )
                    )),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    // Deleting yourself mid-session locks you out of your own panel.
                    ->hidden(fn (User $record): bool => $record->getKey() === auth()->id()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (Collection $records): void {
                            $records
                                ->reject(fn (User $user): bool => $user->getKey() === auth()->id())
                                ->each(fn (User $user) => $user->delete());
                        }),
                ]),
            ])
            ->emptyStateHeading('No staff accounts yet')
            ->emptyStateDescription('Add one, give it a role, and it can sign in.');
    }

    /** @return array<int, string> Roles available to assign, for filters. */
    public static function roleOptions(): array
    {
        return Role::query()->where('guard_name', 'web')->pluck('name')->all();
    }
}
