<?php

namespace App\Features\Profile\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ProfileService
{
    /**
     * Update user personal information.
     *
     * @param array{name: string, username: string, email: string} $data
     */
    public function updateProfile(User $user, array $data): bool
    {
        return $user->update([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
        ]);
    }

    /**
     * Update user password.
     */
    public function updatePassword(User $user, string $newPassword): bool
    {
        return $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }

    /**
     * Crop image to 1:1 ratio and save as user's profile photo.
     *
     * @param string $imageData Base64 data URI or raw image contents or local path
     */
    public function updateProfilePhoto(User $user, string $imageData): string
    {
        // Decode base64 if data URI
        if (str_contains($imageData, ';base64,')) {
            [, $content] = explode(';base64,', $imageData);
            $binary = base64_decode($content);
        } elseif (file_exists($imageData)) {
            $binary = file_get_contents($imageData);
        } else {
            $binary = $imageData;
        }

        $sourceImage = @imagecreatefromstring($binary);
        if (!$sourceImage) {
            throw new RuntimeException('Format gambar tidak valid atau tidak didukung.');
        }

        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);

        // Calculate center square crop coordinates
        $cropSize = min($origWidth, $origHeight);
        $srcX = (int) (($origWidth - $cropSize) / 2);
        $srcY = (int) (($origHeight - $cropSize) / 2);

        $targetSize = 400; // Standard 1:1 avatar resolution
        $croppedCanvas = imagecreatetruecolor($targetSize, $targetSize);

        // Preserve PNG transparency
        imagealphablending($croppedCanvas, false);
        imagesavealpha($croppedCanvas, true);

        imagecopyresampled(
            $croppedCanvas,
            $sourceImage,
            0,
            0,
            $srcX,
            $srcY,
            $targetSize,
            $targetSize,
            $cropSize,
            $cropSize
        );

        ob_start();
        imagepng($croppedCanvas, null, 8);
        $outputBinary = ob_get_clean();

        imagedestroy($sourceImage);
        imagedestroy($croppedCanvas);

        // Delete old photo if present
        $this->deleteProfilePhoto($user);

        $filename = 'profile-photos/'.Str::random(40).'.png';
        Storage::disk('public')->put($filename, $outputBinary);

        $user->update(['profile_photo_path' => $filename]);

        return $filename;
    }

    /**
     * Delete existing profile photo.
     */
    public function deleteProfilePhoto(User $user): bool
    {
        if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        return $user->update(['profile_photo_path' => null]);
    }
}

