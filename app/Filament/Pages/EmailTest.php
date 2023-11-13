<?php

namespace App\Filament\Pages;

use Filament\Forms\Components;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class EmailTest extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.email-test';

    public ?string $from = '';

    public ?string $to = '';

    public function mount(): void
    {
        $this->form->fill([
            'from' => config('mail.from.address'),
            'to' => '',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\TextInput::make('from')
                    ->label('Email From')
                    ->required(),
                Components\TextInput::make('to')
                    ->label('Email To')
                    ->required(),
                Components\Actions::make([
                    Components\Actions\Action::make('Send')
                        ->button()
                        ->submit('form'),
                    //                    ->action(fn () => $this->generate()),
                ]),
            ]);
    }

    public function sendEmail(): void
    {
        try {
            $form = $this->validate();
            Mail::html('This is a test message.', function (Message $message) use ($form) {
                $message->subject('Test Message');
                $message->from($form['from']);
                $message->to($form['to']);
            });
        } catch (\GuzzleHttp\Exception\ClientException  $e) {
            dd([
                'HTTP Code' => $e->getCode(),
                'Response' => $e->getResponse()->getBody()->getContents(),
            ]);
        } catch (\Exception $e) {
            dd($e);
        }

        Notification::make()
            ->title('Message sent without error')
            ->success()
            ->send();
    }
}
