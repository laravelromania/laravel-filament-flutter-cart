<?php

declare(strict_types=1);

namespace Modules\Payments\Drivers;

use Illuminate\Http\Request;
use Modules\Core\Contracts\Payable;
use Modules\Core\DataObjects\PaymentRedirect;
use Modules\Core\DataObjects\PaymentResult;

/**
 * Netopia Payments (fostul mobilPay) — cel mai folosit procesator de carduri din
 * România. În PRODUCȚIE cererea de plată e un document criptat RSA cu certificatul
 * public al Netopia (env_key + data), iar IPN-ul îl decriptezi cu cheia ta privată;
 * autenticitatea vine din perechea de chei. Aici, fără cont de comerciant, driverul
 * rulează în SANDBOX: {@see AbstractGateway} preia tot fluxul prin simulatorul intern.
 */
class NetopiaProvider extends AbstractGateway
{
    public function __construct(
        private readonly string $signature,
        private readonly string $publicCert,
        private readonly string $privateKey,
        bool $sandbox,
        private readonly string $baseUrl,
        private readonly string $label,
        string $sandboxSecret,
    ) {
        parent::__construct($sandbox, $sandboxSecret);
    }

    public function code(): string
    {
        return 'netopia';
    }

    public function label(): string
    {
        return $this->label;
    }

    public function isSandbox(): bool
    {
        // No signature / cert ⇒ we could never sign a live request or verify a
        // live IPN, so we stay in sandbox no matter what the flag says.
        return $this->sandbox
            || $this->signature === ''
            || $this->publicCert === '';
    }

    protected function initiateLive(Payable $order): PaymentRedirect
    {
        // PRODUCȚIE: construiește documentul de plată (semnătura ta, suma, moneda,
        // return/confirm URL, order id = referința comenzii) și îl criptezi cu
        // certificatul public al Netopia — openssl_seal produce perechea env_key +
        // data pe care le trimiți ca form-POST către $baseUrl.
        //
        //   openssl_seal($xml, $data, $envKeys, [$this->publicCert], 'RC4');
        //   $fields = ['env_key' => base64_encode($envKeys[0]), 'data' => base64_encode($data)];
        return new PaymentRedirect(
            url: $this->baseUrl,
            method: 'POST',
            fields: [
                'signature' => $this->signature,
                'env_key' => '', // base64(env_key) din openssl_seal
                'data' => '',    // base64(data) din openssl_seal
            ],
        );
    }

    protected function handleLiveCallback(Request $request): PaymentResult
    {
        // PRODUCȚIE: IPN-ul vine ca env_key + data. Îl decriptezi cu cheia privată
        // (openssl_open), parsezi XML-ul <order>...<mobilpay> și citești action
        // (confirmed / paid / canceled) + error code. Vezi verifyLiveSignature().
        return new PaymentResult(success: false, reference: '', rawStatus: 'unhandled');
    }

    protected function verifyLiveSignature(Request $request): bool
    {
        // PRODUCȚIE: Netopia nu trimite un hash separat — autenticitatea e dată de
        // faptul că doar deținătorul cheii private poate decripta env_key + data.
        // Verifici că openssl_open reușește cu $this->privateKey ȘI că <signature>
        // din payload e semnătura ta de comerciant. Fără chei, respingem.
        return false;
    }

    protected function refundLive(Payable $order): bool
    {
        // PRODUCȚIE: apel „credit"/„refund" către API-ul Netopia cu id-ul tranzacției.
        return false;
    }
}
