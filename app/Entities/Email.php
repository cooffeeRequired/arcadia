<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'emails')]
class Email implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'id')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, name: 'subject')]
    private string $subject;

    #[ORM\Column(type: 'text', name: 'body')]
    private string $body;

    #[ORM\Column(type: 'string', length: 255, name: 'from_email')]
    private string $from_email;

    #[ORM\Column(type: 'string', length: 255, name: 'from_name')]
    private string $from_name;

    #[ORM\Column(type: 'text', name: 'to_emails')]
    private string $to_emails; // JSON array of email addresses

    #[ORM\Column(type: 'text', nullable: true, name: 'cc_emails')]
    private ?string $cc_emails;

    #[ORM\Column(type: 'text', nullable: true, name: 'bcc_emails')]
    private ?string $bcc_emails;

    #[ORM\Column(type: 'text', nullable: true, name: 'attachments')]
    private ?string $attachments;

    #[ORM\Column(type: 'string', length: 50, name: 'status')]
    private string $status = 'draft'; // draft, sent, failed, scheduled

    #[ORM\Column(type: 'datetime', nullable: true, name: 'sent_at')]
    private ?\DateTime $sent_at = null;

    #[ORM\Column(type: 'datetime', nullable: true, name: 'scheduled_at')]
    private ?\DateTime $scheduled_at = null;

    #[ORM\Column(type: 'text', nullable: true, name: 'error_message')]
    private ?string $error_message = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Customer::class)]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id')]
    private ?Customer $customer = null;

    #[ORM\ManyToOne(targetEntity: Deal::class)]
    #[ORM\JoinColumn(name: 'deal_id', referencedColumnName: 'id')]
    private ?Deal $deal = null;

    #[ORM\ManyToOne(targetEntity: EmailTemplate::class)]
    #[ORM\JoinColumn(name: 'template_id', referencedColumnName: 'id')]
    private ?EmailTemplate $template = null;

    #[ORM\ManyToOne(targetEntity: EmailServer::class)]
    #[ORM\JoinColumn(name: 'server_id', referencedColumnName: 'id')]
    private ?EmailServer $server = null;

    #[ORM\Column(type: 'datetime', name: 'created_at')]
    private \DateTime $created_at;

    #[ORM\Column(type: 'datetime', name: 'updated_at')]
    private \DateTime $updated_at;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getSubject(): string { return $this->subject; }
    public function getBody(): string { return $this->body; }
    public function getFromEmail(): string { return $this->from_email; }
    public function getFromName(): string { return $this->from_name; }
    public function getToEmails(): string { return $this->to_emails; }
    public function getCcEmails(): ?string { return $this->cc_emails; }
    public function getBccEmails(): ?string { return $this->bcc_emails; }
    public function getAttachments(): ?string { return $this->attachments; }
    public function getStatus(): string { return $this->status; }
    public function getSentAt(): ?\DateTime { return $this->sent_at; }
    public function getScheduledAt(): ?\DateTime { return $this->scheduled_at; }
    public function getErrorMessage(): ?string { return $this->error_message; }
    public function getUser(): User { return $this->user; }
    public function getCustomer(): ?Customer { return $this->customer; }
    public function getDeal(): ?Deal { return $this->deal; }
    public function getTemplate(): ?EmailTemplate { return $this->template; }
    public function getServer(): ?EmailServer { return $this->server; }
    public function getCreatedAt(): \DateTime { return $this->created_at; }
    public function getUpdatedAt(): \DateTime { return $this->updated_at; }

    // Setters
    public function setSubject(string $subject): self { $this->subject = $subject; return $this; }
    public function setBody(string $body): self { $this->body = $body; return $this; }
    public function setFromEmail(string $from_email): self { $this->from_email = $from_email; return $this; }
    public function setFromName(string $from_name): self { $this->from_name = $from_name; return $this; }
    public function setToEmails(string $to_emails): self { $this->to_emails = $to_emails; return $this; }
    public function setCcEmails(?string $cc_emails): self { $this->cc_emails = $cc_emails; return $this; }
    public function setBccEmails(?string $bcc_emails): self { $this->bcc_emails = $bcc_emails; return $this; }
    public function setAttachments(?string $attachments): self { $this->attachments = $attachments; return $this; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function setSentAt(?\DateTime $sent_at): self { $this->sent_at = $sent_at; return $this; }
    public function setScheduledAt(?\DateTime $scheduled_at): self { $this->scheduled_at = $scheduled_at; return $this; }
    public function setErrorMessage(?string $error_message): self { $this->error_message = $error_message; return $this; }
    public function setUser(User $user): self { $this->user = $user; return $this; }
    public function setCustomer(?Customer $customer): self { $this->customer = $customer; return $this; }
    public function setDeal(?Deal $deal): self { $this->deal = $deal; return $this; }
    public function setTemplate(?EmailTemplate $template): self { $this->template = $template; return $this; }
    public function setServer(?EmailServer $server): self { $this->server = $server; return $this; }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updated_at = new \DateTime();
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'subject' => $this->getSubject(),
            'body' => $this->getBody(),
            'from_email' => $this->getFromEmail(),
            'from_name' => $this->getFromName(),
            'to_emails' => $this->getToEmails(),
            'cc_emails' => $this->getCcEmails(),
            'bcc_emails' => $this->getBccEmails(),
            'attachments' => $this->getAttachments(),
            'status' => $this->getStatus(),
            'sent_at' => $this->getSentAt()?->format('c'),
            'scheduled_at' => $this->getScheduledAt()?->format('c'),
            'error_message' => $this->getErrorMessage(),
            'user_id' => $this->getUser()->getId(),
            'customer_id' => $this->getCustomer()?->getId(),
            'deal_id' => $this->getDeal()?->getId(),
            'template_id' => $this->getTemplate()?->getId(),
            'server_id' => $this->getServer()?->getId(),
            'created_at' => $this->getCreatedAt()->format('c'),
            'updated_at' => $this->getUpdatedAt()->format('c')
        ];
    }
}
