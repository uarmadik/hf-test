<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CreditRepository")
 */
class Credit
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $credit_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * 1 - consumerloans; 2 - mortgages; 3 - autoloans;
     * @ORM\Column(type="smallint")
     */
    private $type;

    /**
     * @ORM\Column(type="integer")
     */
    private $bank_id;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $period;

    /**
     * @ORM\Column(type="decimal", precision=4, scale=2, nullable=true)
     */
    private $rate;

    private $overpay = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getBankId(): ?int
    {
        return $this->bank_id;
    }

    public function setBankId(int $bank_id): self
    {
        $this->bank_id = $bank_id;

        return $this;
    }

    public function getCreditId(): ?int
    {
        return $this->credit_id;
    }

    public function setCreditId(int $credit_id): self
    {
        $this->credit_id = $credit_id;

        return $this;
    }

    public function getPeriod(): ?int
    {
        return $this->period;
    }

    public function setPeriod(?int $period): self
    {
        $this->period = $period;

        return $this;
    }

    public function getRate()
    {
        return $this->rate;
    }

    public function setRate($rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * @param int $sum
     * @param int $period - days
     * @return float|int
     */
    public function setOverpay(int $sum, int $period) {
        $this->overpay = $sum * ($this->getRate() / 100) * round(($period / 365), 3);
    }

    public function getOverpay()
    {
        return $this->overpay;
    }
}
