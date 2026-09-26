<?php
declare(strict_types=1);

class Transaction {
    public function __construct(
        private string $id,
        private string $type,
        private float $amount
    ) {}

    public function getId(): string { return $this->id; }
    public function getType(): string { return $this->type; }
    public function getAmount(): float { return $this->amount; }

    public function process(float &$sessionBalance): bool|string {
        return match($this->type) {
            'deposit' => $this->handleDeposit($sessionBalance),
            'penarikan' => $this->handleWithdrawal($sessionBalance),
            default => 'Jenis transaksi tidak valid.'
        };
    }

    private function handleDeposit(float &$sessionBalance): bool {
        $sessionBalance += $this->amount;
        return true;
    }

    private function handleWithdrawal(float &$sessionBalance): bool|string {
        if ($sessionBalance < $this->amount) {
            return 'Gagal: Saldo tidak mencukupi untuk penarikan ini.';
        }
        $sessionBalance -= $this->amount;
        return true;
    }
}
?>