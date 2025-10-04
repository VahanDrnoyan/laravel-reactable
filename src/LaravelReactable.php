<?php

namespace TrueFans\LaravelReactable;

class LaravelReactable
{
    /**
     * Get all available reaction types.
     */
    public function getReactionTypes(): array
    {
        return config('reactable.reaction_types', []);
    }

    /**
     * Get a specific reaction type configuration.
     */
    public function getReactionConfig(string $type): ?array
    {
        return config("reactable.reaction_types.{$type}");
    }

    /**
     * Check if a reaction type is valid.
     */
    public function isValidReaction(string $type): bool
    {
        return array_key_exists($type, $this->getReactionTypes());
    }

    /**
     * Get all reaction type keys.
     */
    public function getReactionTypeKeys(): array
    {
        return array_keys($this->getReactionTypes());
    }

    /**
     * Get display settings.
     */
    public function getDisplaySettings(): array
    {
        return config('reactable.display', []);
    }
}
