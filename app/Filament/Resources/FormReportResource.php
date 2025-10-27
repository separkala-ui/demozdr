<?php

namespace App\Filament\Resources;

use App\Models\DynamicForms\FormReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FormReportResource extends Resource
{
    protected static ?string $model = FormReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    
    protected static ?string $navigationGroup = 'فرم‌های عملیاتی';
    
    protected static ?string $navigationLabel = 'نتایج فرم‌ها';
    
    protected static ?string $modelLabel = 'نتیجه فرم';
    
    protected static ?string $pluralModelLabel = 'نتایج فرم‌ها';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('اطلاعات گزارش')
                    ->schema([
                        Forms\Components\Select::make('template_id')
                            ->label('الگوی فرم')
                            ->relationship('template', 'title')
                            ->required()
                            ->disabled(),

                        Forms\Components\TextInput::make('overall_score')
                            ->label('امتیاز کلی')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->label('وضعیت')
                            ->options([
                                'draft' => '📝 پیش‌نویس',
                                'submitted' => '✅ ثبت‌شده',
                                'reviewed' => '👁️ بررسی‌شده',
                                'approved' => '✔️ تایید‌شده',
                                'rejected' => '❌ رد‌شده',
                            ])
                            ->disabled(),

                        Forms\Components\Textarea::make('notes')
                            ->label('یادداشت‌ها')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('پاسخ‌ها')
                    ->schema([
                        Forms\Components\RepeatableEntry::make('answers')
                            ->label('پاسخ‌ها')
                            ->schema([
                                Forms\Components\TextEntry::make('field.label')
                                    ->label('سوال'),
                                Forms\Components\TextEntry::make('value')
                                    ->label('پاسخ'),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('template.title')
                    ->label('فرم')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => [
                        'draft' => '📝 پیش‌نویس',
                        'submitted' => '✅ ثبت‌شده',
                        'reviewed' => '👁️ بررسی‌شده',
                        'approved' => '✔️ تایید‌شده',
                        'rejected' => '❌ رد‌شده',
                    ][$state] ?? $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('overall_score')
                    ->label('امتیاز')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) : '-'),

                Tables\Columns\TextColumn::make('reporter.full_name')
                    ->label('گزارش‌دهنده')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('تاریخ تکمیل')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'draft' => '📝 پیش‌نویس',
                        'submitted' => '✅ ثبت‌شده',
                        'reviewed' => '👁️ بررسی‌شده',
                        'approved' => '✔️ تایید‌شده',
                        'rejected' => '❌ رد‌شده',
                    ]),

                Tables\Filters\SelectFilter::make('template_id')
                    ->label('فرم')
                    ->relationship('template', 'title'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('مشاهده'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\FormReportResource\Pages\ListFormReports::route('/'),
            'view' => \App\Filament\Resources\FormReportResource\Pages\ViewFormReport::route('/{record}'),
        ];
    }
}
