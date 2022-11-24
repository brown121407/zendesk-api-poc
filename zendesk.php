<?php
namespace Zendesk;

class TicketType {
    public const PROBLEM = 'problem';
    public const INCIDENT = 'incident';
    public const QUESTION = 'question';
    public const TASK = 'task';

    public const ALL = [self::PROBLEM, self::INCIDENT, self::QUESTION, self::TASK];
}

class Priority {
    public const URGENT = 'urgent';
    public const HIGH = 'high';
    public const NORMAL = 'normal';
    public const LOW = 'low';

    public const ALL = [self::URGENT, self::HIGH, self::NORMAL, self::LOW];
}

class Tag {
    public const CONTACT = 'contact';
    public const ADAUGA_O_SALA = 'adauga-o-sala';
    public const FEEDBACK = 'feedback';
    public const CORPORATE = 'corporate';
    public const BUG = 'bug';

    public const ALL = [self::CONTACT, self::ADAUGA_O_SALA, self::FEEDBACK, self::CORPORATE, self::BUG];
}

class Ticket {
    private string $subject;
    private string $body;
    private array $tags = [];
    private string $recipient = 'hello@esx.ro';
    private array $uploads = [];
    private ?string $type = null;
    private ?array $requester = null;
    private ?string $priority = null;

    public function setSubject(string $subject) {
        $this->subject = $subject;
        return $this;
    }

    public function setBody(string $body) {
        $this->body = $body;
        return $this;
    }

    public function setType(string $type) {
        if (!in_array($type, TicketType::ALL)) {
            throw new Exception("Invalid ticket type: $type.");
        }

        $this->type = $type;
        return $this;
    }

    public function setTags(array $tags) {
        foreach ($tags as $tag) {
            if (!in_array($tag, Tag::ALL)) {
                throw new Exception("Invalid ticket tag: $tag.");
            }
        }
        $this->tags = $tags;
        return $this;
    }

    public function setRecipient(string $recipient) {
        $this->recipient = $recipient;
        return $this;
    }

    public function setUploads(array $uploads) {
        $this->uploads = $uploads;
        return $this;
    }

    public function setRequester(string $name, string $email) {
        $this->requester = [
            'name' => $name,
            'email' => $email,
        ];
        return $this;
    }

    public function setPriority(string $priority) {
        if (!in_array($priority, Priority::ALL)) {
            throw new Exception("Invalid ticket priority: $priority.");
        }

        $this->priority = $priority;
        return $this;
    }

    public function toArray() {
        $ticket = [
            'subject' => $this->subject,
            'comment' => [
                'body' => $this->body,
                'uploads' => $this->uploads,
            ],
            'tags' => $this->tags,
            'recipient' => $this->recipient,
        ];

        if ($this->requester) {
            $ticket['requester'] = $this->requester;
        }

        if ($this->type) {
            $ticket['type'] = $this->type;
        }

        if ($this->priority) {
            $ticket['priority'] = $this->priority;
        }

        return ['ticket' => $ticket];
    }
}

class API {
    private string $apiUrl;
    private string $apiToken;

    function __construct() {
        $this->apiUrl = getenv('ZENDESK_API_URL');
        $this->apiToken = base64_encode(getenv('ZENDESK_API_TOKEN'));
    }

    function createTicket(Ticket $ticket) {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl . 'tickets.json',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic ' . $this->apiToken,
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($ticket->toArray()),
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    function uploadFile($filePath, $fileName, $mime) {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl . 'uploads.json?' . http_build_query(['filename' => $fileName]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: '. $mime,
                'Authorization: Basic ' . $this->apiToken,
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => file_get_contents($filePath),
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}
