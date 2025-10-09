<?php

namespace App\Mail;

use App\Models\ClientUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AbandonedCartReminder extends Mailable
{
    use Queueable, SerializesModels;

    public ClientUser $client;
    public $cartItems;
    public $cartTotal;

    /**
     * Create a new message instance.
     */
    public function __construct(ClientUser $client)
    {
        $this->client = $client;
        
        // Carrega os itens do carrinho com relacionamentos necessários
        $this->cartItems = $client->cartItems()
            ->with([
                'product.productable.artists', 
                'product.productable.vinylSec'
            ])
            ->get();
            
        // Calcula o total do carrinho de forma segura
        $this->cartTotal = $client->cart_total ?? 0;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Você esqueceu alguns itens no seu carrinho - RDV Discos',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.abandoned_cart_reminder',
            with: [
                'client' => $this->client,
                'cartItems' => $this->cartItems,
                'cartTotal' => $this->cartTotal,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
