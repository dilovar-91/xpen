<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Filament\Resources\Receipts\ReceiptResource;
use App\Models\Receipt;
use App\Models\Showroom;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;

class ListReceiptByShowroom extends ListRecords
{
    protected static string $resource = ReceiptResource::class;

    public ?Showroom $showroom = null;

    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public ?string $type = null;

    public int $showroomId = 0;



    public function setToday(): void
    {
        $today = now()->toDateString();
        $this->dateFrom = $today;
        $this->dateTo = $today;
    }



    public function resetWeek(): void
    {
        $this->dateFrom = Carbon::now()->startOfWeek()->toDateString();
        $this->dateTo   = Carbon::now()->endOfWeek()->toDateString();

        $this->dispatch('$refresh');
    }

    public function clearDates(): void
    {
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->type = null;

        // ✅ Если showroom куда-то делся — восстанавливаем
        if (! $this->showroom && $this->showroomId) {
            $this->showroom = Showroom::find($this->showroomId);
        }

        $this->dispatch('refreshTable');
    }

    public function mount(): void
    {
        $showroomParam = request()->route('showroom');
        $this->showroomId = $showroomParam->id;

        if ($this->showroomId > 0) {
            $this->showroom = Showroom::findOrFail($this->showroomId);
        }

        $today = Carbon::today()->toDateString();
        $this->dateFrom = null;
        $this->dateTo = null;



        $user = Auth::user();

        if (($user->role !== 'admin' && $user->role !== 'kassa' ) && $user->showroom_id != $this->showroomId) {
            abort(403, 'У вас нет доступа к этому салону.');
        }
    }

    public function getTitle(): string
    {
        return '';
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->makeAction('addReceipt'),
        ];
    }


    protected function getTableQuery(): Relation|Builder|null
    {

        //dd($this->type);
        return Receipt::query()
            ->when($this->type, fn($q) => $q->where('type_id', $this->type))
            ->when($this->showroomId, fn ($q) => $q->where('showroom_id', $this->showroomId))->with('items');
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.pages.expense') => 'Расходы',
            $this->showroom?->name ?? 'Салон'
        ];
    }

    protected function makeAction(string $type)
    {
        return match ($type) {
            'addReceipt' => Action::make('addReceipt')
                ->label('Добавить расписку')
                ->icon('heroicon-o-arrow-up-circle')
                ->color('success')
                ->slideOver()
                ->form(fn () => ReceiptResource::getReceiptForm(1))
                ->action(fn (array $data) => Receipt::create($data)),
        };
    }
}
