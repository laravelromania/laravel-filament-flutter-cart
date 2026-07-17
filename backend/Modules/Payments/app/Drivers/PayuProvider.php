<?php

declare(strict_types=1);

namespace Modules\Payments\Drivers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Modules\Core\Contracts\Payable;
use Modules\Core\DataObjects\PaymentRedirect;
use Modules\Core\DataObjects\PaymentResult;

/**
 * PayU (grupul Naspers) — al doilea procesator major din România. În PRODUCȚIE
 * ceri un token OAuth, creezi comanda prin REST API și primești un `redirectUri`
 * către pagina de card; IPN-ul e semnat cu antetul `OpenPayU-Signature` (un hash
 * al corpului cererii cu cheia ta secretă), pe care îl recalculezi și îl compari.
 * Fără credențiale, driverul rulează în SANDBOX prin {@see AbstractGateway}.
 */
class PayuProvider extends AbstractGateway
{
    public function __construct(
        private readonly string $merchant,
        private readonly string $posId,
        private readonly string $secret,
        bool $sandbox,
        private readonly string $baseUrl,
        private readonly string $label,
        string $sandboxSecret,
    ) {
        parent::__construct($sandbox, $sandboxSecret);
    }

    public function code(): string
    {
        return 'payu';
    }

    public function label(): string
    {
        return $this->label;
    }

    public function isSandbox(): bool
    {
        return $this->sandbox
            || $this->posId === ''
            || $this->secret === '';
    }

    protected function initiateLive(Payable $order): PaymentRedirect
    {
        // PRODUCȚIE: OAuth → creezi comanda → primești redirectUri.
        $token = $this->client()
            ->asForm()
            ->post($this->baseUrl.'/pl/standard/user/oauth/authorize', [
                'grant_type' => 'client_credentials',
                'client_id' => $this->posId,
                'client_secret' => $this->secret,
            ])
            ->throw()
            ->json('access_token');

        $redirectUri = $this->client()
            ->withToken((string) $token)
            ->post($this->baseUrl.'/api/v2_1/orders', [
                'merchantPosId' => $this->posId,
                'currencyCode' => 'RON',
                'totalAmount' => (string) $order->payableAmount()->getMinorAmount(),
                'extOrderId' => $order->payableReference(),
                'description' => 'Comanda '.$order->payableReference(),
                'notifyUrl' => route('payments.callback', ['gateway' => $this->code()]),
                'continueUrl' => route('payments.return', [
                    'gateway' => $this->code(),
                    'reference' => $order->payableReference(),
                ]),
            ])
            ->throw()
            ->json('redirectUri');

        return new PaymentRedirect(url: (string) $redirectUri);
    }

    protected function handleLiveCallback(Request $request): PaymentResult
    {
        // PRODUCȚIE: corpul IPN e {"order": {"status": "COMPLETED", "extOrderId": "..."}}.
        $order = (array) $request->input('order', []);
        $status = (string) ($order['status'] ?? '');

        return new PaymentResult(
            success: $status === 'COMPLETED',
            reference: (string) ($order['extOrderId'] ?? ''),
            rawStatus: $status !== '' ? $status : 'unknown',
        );
    }

    protected function verifyLiveSignature(Request $request): bool
    {
        // PRODUCȚIE: antetul `OpenPayU-Signature: signature=<hash>;algorithm=<algo>`.
        // Recalculezi hash-ul peste corpul BRUT + cheia secretă și compari constant.
        $header = (string) $request->header('OpenPayU-Signature', '');
        parse_str(str_replace(';', '&', $header), $parts);

        $incoming = (string) ($parts['signature'] ?? '');
        $algorithm = strtolower((string) ($parts['algorithm'] ?? 'md5'));
        $expected = hash($algorithm === '' ? 'md5' : $algorithm, $request->getContent().$this->secret);

        return $incoming !== '' && hash_equals($expected, $incoming);
    }

    protected function refundLive(Payable $order): bool
    {
        // PRODUCȚIE: POST /api/v2_1/orders/{orderId}/refunds cu token-ul OAuth.
        return false;
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()->asJson();
    }
}
