<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Filament\Resources\ReceiptItems\ReceiptItemResource;
use App\Filament\Resources\Receipts\ReceiptResource;
use App\Models\Expense;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\Showroom;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;

class ListReceiptDetail extends ListRecords
{
    protected static string $resource = ReceiptItemResource::class;

    public ?Showroom $showroom = null;

    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public ?string $type = null;
    public ?string $tag = null;
    public $id = 0;

    public int $showroomId = 0;


    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('date')
                    ->label('Дата')
                    ->date()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Сумма')
                    ->money('RUB', true)
                    ->sortable(),

                TextColumn::make('comment')
                    ->label('Комментарий')
                    ->limit(40)
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                EditAction::make()
                    ->slideOver()
                    ->form(fn(ReceiptItem $record) => self::getReceiptItemForm($record->receipt_id))
                    ->action(fn(ReceiptItem $record, array $data) => $record->update($data)),

                DeleteAction::make(),
            ])
            ->defaultSort('date', 'desc');
    }


    public function mount(): void
    {
        $showroomParam = request()->route('showroom');
        $id = request()->route('id');
        $this->showroomId = $showroomParam->id ?? null;

        if ($this->showroomId > 0) {
            $this->showroom = Showroom::findOrFail($this->showroomId);
        }

        $this->id = $id;


        $user = Auth::user();

        if (($user->role !== 'admin' && $user->role !== 'kassa') && $user->showroom_id != $this->showroomId) {
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
            $this->makeAction('addReceiptItem'),
            $this->makeAction('closeReceipt'),
        ];
    }


    protected function getTableQuery(): Relation|Builder|null
    {

        //dd($this->type);
        return ReceiptItem::query()
            ->where('receipt_id', $this->id)
            ->when($this->type, fn($q) => $q->where('type_id', $this->type));
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
            'addReceiptItem' => Action::make('addReceiptItem')
                ->label('Добавить оплату по расписке')
                ->icon('heroicon-o-arrow-up-circle')
                ->color('success')
                ->slideOver()
                ->form(fn() => $this->getReceiptItemForm($this->id))
                ->action(fn(array $data) => ReceiptItem::create($data)),
            'closeReceipt' => Action::make('closeReceipt')
                ->label('Расписка погашена')
                ->icon('heroicon-o-check')
                ->button()
                ->size('xs')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {

                    $receiptId = (int)$this->id;

                    $receipt = Receipt::query()->find($receiptId);

                    if (!$receipt) {
                        Notification::make()
                            ->title('Расписка не найдена')
                            ->danger()
                            ->send();

                        return;
                    }

                    $paidSum = ReceiptItem::query()
                        ->where('receipt_id', $receiptId)
                        ->sum('amount');

                    // сравнение денег безопасно (2 знака после запятой)
                    if (bccomp((string)$paidSum, (string)$receipt->full_price, 2) !== 0) {
                        Notification::make()
                            ->title('Невозможно закрыть расписку')
                            ->body(
                                'Сумма оплат (' . number_format($paidSum, 2) .
                                ') не равна полной сумме расписки (' .
                                number_format($receipt->full_price, 2) . ')'
                            )
                            ->danger()
                            ->send();

                        return;
                    }

                    $receipt->update(['closed' => true,
                        'closed_date' => now()->toDateString(),]);
                    $expenseComment = "Деньги по расписке {$receipt->full_name} {$receipt->comment}";
                    $data = ['type_id'=>1, 'income_type'=>1,  'showroom_id'=>$receipt->showroom_id, 'date'=>$receipt->closed_date, 'comment'=>$expenseComment, 'income'=>$receipt->full_price];
                    Expense::create($data);



                    Notification::make()
                        ->title('Расписка закрыта')
                        ->success()
                        ->send();
                })
        };
    }


    public static function getReceiptItemForm(int $id): array
    {


        return [


            Select::make('receipt_id')
                ->label('Расписка')
                ->options(fn() => Receipt::query()
                    ->whereKey($id)
                    ->get()
                    ->mapWithKeys(fn($receipt) => [
                        $receipt->id => "{$receipt->full_name}, {$receipt->phone}",
                    ])
                    ->toArray()
                )
                ->default(fn() => $id)
                ->disabled()
                ->dehydrated() // важно: сохраняет значение, даже если disabled
                ->searchable()
                ->preload(),

            // Салон — автозаполнение, только для админа редактируем


            TextInput::make('amount')
                ->label('Сумма погашение')
                ->required()
                ->numeric()
                ->minValue(0),


            DatePicker::make('date')
                ->label('Дата погашения'),

            // Комментарий
            Textarea::make('comment')
                ->label('Комментарий')
                ->columnSpanFull(),
        ];
    }
}
