<?php

namespace Siwapp\InvoiceBundle\Entity;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\Inflector;
use Siwapp\CoreBundle\Entity\AbstractInvoice;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Siwapp\InvoiceBundle\Entity\Invoice
 *
 * @ORM\Table(indexes={
 *    @ORM\Index(name="invoice_cstnm_idx", columns={"customer_name"}),
 *    @ORM\Index(name="invoice_cstid_idx", columns={"customer_identification"}),
 *    @ORM\Index(name="invoice_cstml_idx", columns={"customer_email"}),
 *    @ORM\Index(name="invoice_cntct_idx", columns={"contact_person"})
 * })
 * @ORM\Entity(repositoryClass="Siwapp\InvoiceBundle\Repository\InvoiceRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Invoice extends AbstractInvoice
{
    /**
     * @ORM\OneToMany(targetEntity="Item", mappedBy="invoice", orphanRemoval=true, cascade={"all"})
     */
    private $items;

    /**
     * @ORM\OneToMany(targetEntity="Payment", mappedBy="invoice", orphanRemoval=true, cascade={"all"})
     *
     */
    private $payments;

    /**
     * @ORM\ManyToOne(targetEntity="Siwapp\CoreBundle\Entity\Serie")
     * @ORM\JoinColumn(name="serie_id", referencedColumnName="id")
     *
     * unidirectional many-to-one
     */
    private $serie;


    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->payments = new ArrayCollection();
    }

    /**
     * @var boolean $sent_by_email
     *
     * @ORM\Column(name="sent_by_email", type="boolean", nullable=true)
     */
    private $sent_by_email;

    /**
     * @var integer $number
     *
     * @ORM\Column(name="number", type="integer", nullable=true)
     */
    private $number;

    /**
     * @var bigint $recurring_invoice_id
     *
     * @ORM\Column(name="recurring_invoice_id", type="bigint", nullable=true)
     */
    private $recurring_invoice_id;

    /**
     * @var date $issue_date
     *
     * @ORM\Column(name="issue_date", type="date", nullable=true)
     */
    private $issue_date;

    /**
     * @var date $due_date
     *
     * @ORM\Column(name="due_date", type="date", nullable=true)
     * @Assert\Date()
     */
    private $due_date;

    /**
     * Get draft
     *
     * @return boolean
     */
    public function isClosed()
    {
        return $this->status === Invoice::CLOSED;
    }

    /**
     * Get draft
     *
     * @return boolean
     */
    public function isOpen()
    {
        return in_array($this->status, [Invoice::OPENED, Invoice::OVERDUE], true);
    }

    /**
     * Get draft
     *
     * @return boolean
     */
    public function isDraft()
    {
        return $this->status === Invoice::DRAFT;
    }

    /**
     * Set sent_by_email
     *
     * @param boolean $sentByEmail
     */
    public function setSentByEmail($sentByEmail)
    {
        $this->sent_by_email = $sentByEmail;
    }

    /**
     * Get sent_by_email
     *
     * @return boolean
     */
    public function getSentByEmail()
    {
        return $this->sent_by_email;
    }

    /**
     * Set number
     *
     * @param integer $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * Get number
     *
     * @return integer
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set recurring_invoice_id
     *
     * @param bigint $recurringInvoiceId
     */
    public function setRecurringInvoiceId($recurringInvoiceId)
    {
        $this->recurring_invoice_id = $recurringInvoiceId;
    }

    /**
     * Get recurring_invoice_id
     *
     * @return bigint
     */
    public function getRecurringInvoiceId()
    {
        return $this->recurring_invoice_id;
    }

    /**
     * Set issue_date
     *
     * @param date $issueDate
     */
    public function setIssueDate($issueDate)
    {
        $this->issue_date = $issueDate instanceof \DateTime ?
	  $issueDate: new \DateTime($issueDate);
    }

    /**
     * Get issue_date
     *
     * @return date
     */
    public function getIssueDate()
    {
        return $this->issue_date;
    }

    /**
     * Set due_date
     *
     * @param date $dueDate
     */
    public function setDueDate($dueDate)
    {
      $this->due_date = $dueDate instanceof \DateTime ?
	$dueDate : new \DateTime($dueDate);
    }

    /**
     * Get due_date
     *
     * @return date
     */
    public function getDueDate()
    {
        return $this->due_date;
    }

    /**
     * Add items
     *
     * @param Siwapp\InvoiceBundle\Entity\Item $item
     */
    public function addItem(\Siwapp\InvoiceBundle\Entity\Item $item)
    {
        $this->items[] = $item;
        $item->setInvoice($this);
        $this->setAmounts();
    }

    /**
     * Get items
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Add payments
     *
     * @param Siwapp\InvoiceBundle\Entity\Payment $payments
     */
    public function addPayment(\Siwapp\InvoiceBundle\Entity\Payment $payments)
    {
        $this->payments[] = $payments;
    }

    /**
     * Get payments
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * Set serie
     *
     * @param Siwapp\CoreBundle\Entity\Serie $serie
     */
    public function setSerie(\Siwapp\CoreBundle\Entity\Serie $serie)
    {
        $this->checkSerieChanged($serie);
        $this->serie = $serie;
    }

    /**
     * Get serie
     *
     * @return Siwapp\CoreBundle\Entity\Serie
     */
    public function getSerie()
    {
        return $this->serie;
    }

    /** **************** CUSTOM METHODS AND PROPERTIES **************  */

    /**
     * TODO: provide the serie .
     */
    public function __toString()
    {
        return $this->label();
    }

    public function label()
    {
        $series = $this->getSerie();
        $label = '';
        $label .= $series ? $series->getValue() : '';
        $label .= $this->isDraft() ? '[draft]' : $this->getNumber();

        return $label;
    }

    const DRAFT    = 0;
    const CLOSED   = 1;
    const OPENED   = 2;
    const OVERDUE  = 3;

    private $serie_changed = false;

    public function getDueAmount()
    {
        if ($this->isDraft()) {
            return null;
        }
        return $this->getGrossAmount() - $this->getPaidAmount();
    }

    /**
     * try to catch custom methods to be used in twig templates
     */
    public function __get($name)
    {
        if($name == 'due_amount')
          {
              $m = Inflector::camelize("get_{$name}");
              return $this->$m();
          }
        if(strpos($name, 'tax_amount_') === 0)
        {
            return $this->calculate($name, true);
        }
        return false;
    }

    public function __isset($name)
    {
        if($name == 'due_amount')
        {
            return true;
        }
        if(strpos($name, 'tax_amount_') === 0)
        {
            return true;
        }
        // TODO: somebody wrote this, but there is no such parent method
        // return parent::__isset($name);
    }

    /**
     * When setting serie, we check if there has been a serie change,
     * because the invoice number will have to change later
     *
     * TODO: Review this method when serie object are available
     *
     * @author JoeZ99 <jzarate@gmail.com>
     *
     */
    private function checkSerieChanged(\Siwapp\CoreBundle\Entity\Serie $serie)
    {
        if($this->number>0 && $this->serie && $this->serie != $serie)
        {
            $this->serie_changed = true;
        }
    }

    /**
     * checkStatus
     * checks and sets the status
     *
     * @return Siwapp\InvoiceBundle\Invoice $this
     */
    public function checkStatus()
    {
        if($this->status == Invoice::DRAFT)
        {
            return $this;
        }
        if($this->getDueAmount() == 0)
        {
            $this->setStatus(Invoice::CLOSED);
        }
        else
        {
            if($this->getDueDate()->getTimestamp() > strtotime(date('Y-m-d')))
            {
                $this->setStatus(Invoice::OPENED);
            }
            else
            {
                $this->setStatus(Invoice::OVERDUE);
            }
        }
        return $this;
    }

    public function getStatusString()
    {
        switch($this->status)
        {
          case Invoice::DRAFT;
            $status = 'draft';
             break;
          case Invoice::CLOSED;
            $status = 'closed';
            break;
          case Invoice::OPENED;
            $status = 'opened';
            break;
          case Invoice::OVERDUE:
            $status = 'overdue';
            break;
          default:
            $status = 'unknown';
            break;
        }
        return $status;
    }

    public function setAmounts()
    {
        parent::setAmounts();
        $this->setPaidAmount($this->calculate('paid_amount'));

        return $this;
    }

    /**
     * needsNumber
     *
     * checks if invoice need number asignment or reasignment
     * either it has not one yet, and it's not draft  or
     * it's serie has changed and need new numeration
     *
     * @author JoeZ
     * @return boolean
     */
    public function needsNumber()
    {

        return (!$this->number && $this->status!=self::DRAFT) ||
            ($this->serie_changed && $this->status!=self::DRAFT);
    }


    /* ********** LIFECYCLE CALLBACKS *********** */

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setNextNumber(PreUpdateEventArgs $event)
    {
        // compute the number of invoice
        if( (!$this->number && $this->status!=self::DRAFT) ||
            ($this->serie_changed && $this->status!=self::DRAFT)
            )
        {
            $this->serie_changed = false;
            // TODO set number as the next available number for that serie
            $this->setNumber($this->getNextNumber($event->getEntityManager()));
        }
    }

    protected function getNextNumber($em)
    {
        $series = $this->getSerie();
        $repo = $em->getRepository('SiwappInvoiceBundle:Invoice');
        $found = $repo->findBy([
            'status' => [self::DRAFT, '<>'],
            'serie' => $series,
        ]);

        if (count($found) > 0)
        {
          $result = $repo->createQueryBuilder('i')
            ->select('MAX(i.number) AS max_number')
            ->where('i.status <> :status')
            ->andWhere('i.serie = :series')
            ->setParameter('status', self::DRAFT)
            ->setParameter('series', $series)
            ->getQuery()
            ->getSingleResult();

          return $result['max_number'] + 1;
        }
        else
        {
          return $series->getFirstNumber();
        }
    }

}
