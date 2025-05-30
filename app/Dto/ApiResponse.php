<?php

namespace App\Dto;

class ApiResponse implements \JsonSerializable
{
    public function __construct(
        public bool $success,
        public mixed $data = null,
        public ?string $message = null,
        public ?int $status = null
    ) {}

    public function jsonSerialize(): mixed
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'message' => $this->message,
            'status' => $this->status,
        ];
    }
}
