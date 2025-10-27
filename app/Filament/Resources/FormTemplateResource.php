<?php

namespace App\Filament\Resources;

use App\Models\DynamicForms\FormTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FormTemplateResource extends Resource
{
    protected static ?string $model = FormTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationGroup = 'فرم‌های عملیاتی';
    
    protected static ?string $navigationLabel = 'الگوهای فرم';
    
    protected static ?string $modelLabel = 'الگوی فرم';
    
    protected static ?string $pluralModelLabel = 'الگوهای فرم';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('اطلاعات پایه')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('عنوان فرم')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan('full'),

                        Forms\Components\Textarea::make('description')
                            ->label('توضیحات')
                            ->maxLength(65535)
                            ->columnSpan('full'),

                        Forms\Components\Select::make('category')
                            ->label('دسته‌بندی')
                            ->options([
                                'qc' => '🔍 کنترل کیفیت',
                                'inspection' => '🔎 بازرسی شعبه',
                                'production' => '🏭 تولید',
                                'other' => '📋 سایر',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Toggle::make('is_active')
                            ->label('فعال')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('فیلدهای فرم')
                    ->schema([
                        Forms\Components\Repeater::make('fields')
                            ->label('فیلدها')
                            ->relationship('fields')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('نام فیلد')
                                    ->required()
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('label')
                                    ->label('برچسب')
                                    ->required()
                                    ->columnSpan(2),

                                Forms\Components\Select::make('type')
                                    ->label('نوع فیلد')
                                    ->options([
                                        'text' => '📝 متن',
                                        'number' => '🔢 عدد',
                                        'date' => '📅 تاریخ',
                                        'select' => '📌 انتخاب',
                                        'checkbox' => '☑️ چک‌باکس',
                                        'textarea' => '📄 متن طولانی',
                                        'file' => '📎 فایل',
                                    ])
                                    ->required()
                                    ->columnSpan(2)
                                    ->live(),

                                Forms\Components\TextInput::make('order')
                                    ->label('ترتیب')
                                    ->numeric()
                                    ->default(1)
                                    ->columnSpan(1),

                                Forms\Components\Toggle::make('required')
                                    ->label('الزامی')
                                    ->default(true)
                                    ->columnSpan(1),

                                Forms\Components\Textarea::make('options')
                                    ->label('گزینه‌ها (یکی در هر سطر)')
                                    ->visible(fn (Forms\Get $get) => in_array($get('type'), ['select', 'checkbox']))
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('validation')
                                    ->label('قوانین اعتبارسنجی')
                                    ->hint('مثال: numeric|between:50,100')
                                    ->columnSpan(2),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible(),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('دسته‌بندی')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => [
                        'qc' => '🔍 کنترل کیفیت',
                        'inspection' => '🔎 بازرسی شعبه',
                        'production' => '🏭 تولید',
                        'other' => '📋 سایر',
                    ][$state] ?? $state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('fields')
                    ->label('تعداد فیلدها')
                    ->formatStateUsing(fn ($record) => $record->fields()->count())
                    ->alignment('center'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),

                Tables\Columns\TextColumn::make('creator.full_name')
                    ->label('سازنده')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('دسته‌بندی')
                    ->options([
                        'qc' => '🔍 کنترل کیفیت',
                        'inspection' => '🔎 بازرسی شعبه',
                        'production' => '🏭 تولید',
                        'other' => '📋 سایر',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('فعال'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('preview')
                    ->label('پیش‌نمایش')
                    ->icon('heroicon-o-eye')
                    ->url(fn (FormTemplate $record) => route('forms.preview', $record))
                    ->openUrlInNewTab(),
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
            'index' => \App\Filament\Resources\FormTemplateResource\Pages\ListFormTemplates::route('/'),
            'create' => \App\Filament\Resources\FormTemplateResource\Pages\CreateFormTemplate::route('/create'),
            'edit' => \App\Filament\Resources\FormTemplateResource\Pages\EditFormTemplate::route('/{record}/edit'),
        ];
    }
}
