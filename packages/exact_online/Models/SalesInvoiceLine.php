<?php

namespace ExactOnline\Models;

class SalesInvoiceLine
{
    public string $GLAccount;
    public string $Item;
    public ?string $InvoiceId;
    public float $Quantity = 0.0;


    public function fromArray(array $data): self
    {
        $this->setGLAccount($data['GLAccount']);
        $this->setItem($data['Item']);
        $this->setInvoiceId($data['InvoiceID'] ?? null);
        $this->setQuantity($data['Quantity'] ?? null);

        return $this;
    }

    public function getGLAccount(): string
    {
        return $this->GLAccount;
    }

    public function setGLAccount(string $GLAccount): void
    {
        $this->GLAccount = $GLAccount;
    }

    public function getItem(): string
    {
        return $this->Item;
    }

    public function setItem(string $Item): void
    {
        $this->Item = $Item;
    }

    public function getInvoiceId(): ?string
    {
        return $this->InvoiceId;
    }

    public function setInvoiceId(?string $InvoiceId): void
    {
        $this->InvoiceId = $InvoiceId;
    }

    public function getQuantity(): float
    {
        return $this->Quantity;
    }

    public function setQuantity(float $Quantity): void
    {
        $this->Quantity = $Quantity;
    }
}
