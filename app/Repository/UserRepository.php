<?php

namespace App\Repository;

use App\Models\User;
use App\Notifications\InvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;
use Intervention\Image\Facades\Image;

class UserRepository extends BaseRepository
{
    /**
     * @var User
     */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->rules = [];
    }

    public function create(array $data, ?callable $callback = null): object
    {
        DB::beginTransaction();

        try {
            $created = $this->user->create($data);
            if ($callback) $callback($created);
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        DB::commit();

        return $created;
    }

    public function storeInvitation(object $user): object
    {
        DB::beginTransaction();

        try {
            $user->invitation_pin = $this->generateInvitationPin();
            $user->save();

            $user->notify(new InvitationNotification($user->invitation_pin));
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        DB::commit();

        return $user;
    }

    public function generateInvitationPin(): string
    {
        do {
            $pin = random_int(000000, 999999);
        } while ($this->user->whereInvitationPin($pin)->exists());

        return $pin;
    }

    public function update(array $data, int $id): object
    {
        DB::beginTransaction();

        try {
            $updated = $this->user->findOrFail($id);
            $updated->fill($data);
            $updated->save();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        DB::commit();
        return $updated;
    }

    public function createImage($file): array
    {
        DB::beginTransaction();

        try {
            // Store File
            $key = \Str::random(6);
            $file_name = $file->getClientOriginalName();
            $width = 256;
            $height = 256;

            $path = "public/images/users/{$key}";

            if (!Storage::has($path)) Storage::makeDirectory($path, 0777, true, true);

            $img = Image::make($file)->fit($width, $height, function($constraint) {
                $constraint->upsize();
            })->encode('jpg', 80);

            $data["avatar"] = Storage::put("$path/{$file_name}", $img) ? $path.'/'.$file_name : null;
        }
        catch (Exception $exception)
        {
            DB::rollBack();
            throw $exception;
        }

        DB::commit();
        return $data;
    }
}
