<?php

namespace App\Features\Profile\Livewire;

use App\Features\Profile\Services\ProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('Profil Saya - StockApp')]
class Profile extends Component
{
    use WithFileUploads;

    // Profile Information
    public string $name = '';
    public string $username = '';
    public string $email = '';

    // Password Update
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    // Photo Crop State
    public mixed $photo = null;
    public string $croppedPhoto = '';
    public bool $showCropModal = false;
    public bool $confirmingPhotoDeletion = false;

    public function mount(): void
    {
        $user = Auth::user();
        if ($user) {
            $this->name = $user->name;
            $this->username = $user->username;
            $this->email = $user->email;
        }
    }

    public function updatedPhoto(): void
    {
        $this->validate([
            'photo' => ['image', 'max:5120'], // 5MB
        ]);

        $this->showCropModal = true;
    }

    public function closeCropModal(): void
    {
        $this->showCropModal = false;
        $this->photo = null;
        $this->croppedPhoto = '';
    }

    public function updateProfileInformation(ProfileService $service): void
    {
        $user = Auth::user();

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $service->updateProfile($user, [
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
        ]);

        session()->flash('success', 'Identitas profil berhasil diperbarui.');
    }

    public function updatePassword(ProfileService $service): void
    {
        $user = Auth::user();

        $this->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Password saat ini yang Anda masukkan salah.');
            return;
        }

        $service->updatePassword($user, $this->password);
        $this->reset(['current_password', 'password', 'password_confirmation']);

        session()->flash('success', 'Password akun berhasil diubah.');
    }

    public function saveCroppedPhoto(ProfileService $service): void
    {
        if (empty($this->croppedPhoto)) {
            // Fallback: if user didn't crop client-side, use uploaded photo directly
            if ($this->photo) {
                $this->croppedPhoto = $this->photo->getRealPath();
            } else {
                $this->addError('photo', 'Pilih foto profil terlebih dahulu.');
                return;
            }
        }

        $user = Auth::user();
        $service->updateProfilePhoto($user, $this->croppedPhoto);

        $this->closeCropModal();
        session()->flash('success', 'Foto profil (rasio 1:1) berhasil disimpan.');
    }

    public function confirmDeletePhoto(): void
    {
        $this->confirmingPhotoDeletion = true;
    }

    public function cancelDeletePhoto(): void
    {
        $this->confirmingPhotoDeletion = false;
    }

    public function deletePhoto(ProfileService $service): void
    {
        $user = Auth::user();
        $service->deleteProfilePhoto($user);
        $this->confirmingPhotoDeletion = false;

        session()->flash('success', 'Foto profil berhasil dihapus.');
    }

    public function render(): View
    {
        return view('livewire.profile.profile', [
            'user' => Auth::user(),
        ]);
    }
}

