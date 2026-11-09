<?php

namespace App\Features\Admin\Livewire;

use App\Features\Admin\Services\AdminUserService;
use App\Features\Stock\Enums\StockStatus;
use App\Features\Stock\Services\StockExcelService;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('components.layouts.app')]
#[Title('User Stock Barang (Read Only) - Admin')]
class AdminUserStockList extends Component
{
    use WithPagination;

    public User $user;
    public string $search = '';
    public string $statusFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount(User $user): void
    {
        $this->user = $user;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function exportExcel(StockExcelService $excelService): StreamedResponse
    {
        $stocks = $this->user->stocks()->latest('id')->get();
        $safeName = preg_replace('/[^A-Za-z0-9_-]/', '_', $this->user->name);

        return $excelService->exportToResponse($stocks, "stock-barang-{$safeName}-".now()->format('Ymd-His').'.xlsx');
    }

    public function render(AdminUserService $adminService): View
    {
        $stocks = $adminService->getUserStocks(
            $this->user->id,
            $this->statusFilter ?: null,
            $this->search ?: null,
            10
        );

        return view('livewire.admin.admin-user-stock-list', [
            'stocks' => $stocks,
            'statuses' => StockStatus::cases(),
        ]);
    }
}

