<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(fn (User $record): bool => $record->getKey() === auth()->id()),
        ];
    }

    /** The stored hash is never shown back, and an empty field means "unchanged". */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        unset($data['password']);

        return $data;
    }

    /**
     * Stops someone removing their own last way back in.
     *
     * Editing your own roles is legitimate — dropping the one that lets you edit
     * roles at all is how a business locks itself out of its own panel at 6pm on a
     * Friday.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();

        if ($record->getKey() !== auth()->id()) {
            return $data;
        }

        $keepsARole = collect($data['roles'] ?? [])->isNotEmpty();

        if (! $keepsARole) {
            Notification::make()
                ->title('Your roles were left unchanged')
                ->body('Removing every role from your own account would lock you out of the panel. Ask another administrator to do it if you meant to.')
                ->warning()
                ->persistent()
                ->send();

            unset($data['roles']);
        }

        return $data;
    }
}
