<?php
namespace App\Library\RateLimit;

class TokenBucket {

    /**
     * Total number of tokens that this
     * bucket can hold
     *
     * @var int
     */
    private $capacity;

    /**
     * @var Rate
     */
    private $refillRate;
    
    /**
     * @var TokenStorable
     */
    private $storage;


    public function __construct(int $capacity, Rate $refillRate, TokenStorable $storage) {
        $this->capacity   = $capacity;
        $this->refillRate = $refillRate;
        $this->storage    = $storage;

        $this->storage->bootstrap();
    }

    public function consume(int $tokensToConsume = 1) : bool {
        $storedData = $this->storage->deserialize();

        $availableTokens = $storedData->tokensAvailable;
        $lastConsumeTime = $storedData->lastConsumeTime;
        $refillRate      = $this->refillRate->getRefillRate();

        $now = microtime(true);

        $remainingTokens = 0;

        if ($lastConsumeTime === 0.0) {
            $remainingTokens = $availableTokens;

        } else {
            // calculate the number of tokens available
            // after a refill, and then attempt to subtract
            // the consumption amount
            $secondsSinceLastConsume = $now - $lastConsumeTime;

            $remainingTokens = $availableTokens + ($secondsSinceLastConsume * $refillRate);
            $remainingTokens = min($this->capacity, $remainingTokens) - $tokensToConsume;
        }

        if($remainingTokens <= 0) {
            return false;
        }

        $storedData->lastConsumeTime = $now;
        $storedData->tokensAvailable = $remainingTokens;

        $this->storage->serialize($storedData);

        return true;
    }

}