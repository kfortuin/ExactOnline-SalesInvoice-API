<?php

namespace ExactOnline\Models;

class SalesInvoice
{
    public string $orderedBy;
    public string $journal;
    /** @var array<SalesInvoiceLine> */
    public array $salesInvoiceLines = [];
    public string $invoiceTo;
    public string $invoiceDate;


    public function fromArray(array $data): self
    {
        $this->setOrderedBy($data['OrderedBy']);
        $this->setJournal($data['Journal']);
        $this->setSalesInvoiceLines($data['SalesInvoiceLines']);
        $this->setInvoiceTo($data['InvoiceTo'] ?? null);
        $this->setInvoiceDate($data['InvoiceDate'] ?? null);

        return $this;
    }

    public function getOrderedBy(): string
    {
        return $this->orderedBy;
    }

    public function setOrderedBy(string $orderedBy): void
    {
        $this->orderedBy = $orderedBy;
    }

    public function getJournal(): string
    {
        return $this->journal;
    }

    public function setJournal(string $journal): void
    {
        $this->journal = $journal;
    }

    public function getSalesInvoiceLines(): array
    {
        return $this->salesInvoiceLines;
    }

    public function setSalesInvoiceLines(array $salesInvoiceLines): void
    {
        $this->salesInvoiceLines = $salesInvoiceLines;
    }

    public function getInvoiceTo(): string
    {
        return $this->invoiceTo;
    }

    public function setInvoiceTo(string $invoiceTo): void
    {
        $this->invoiceTo = $invoiceTo;
    }

    public function getInvoiceDate(): string
    {
        return $this->invoiceDate;
    }

    public function setInvoiceDate(string $invoiceDate): void
    {
        $this->invoiceDate = $invoiceDate;
    }
}
