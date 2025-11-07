<?php

namespace codechap\ai\Traits;

trait PropertyAccessTrait
{
    /**
     * Gets the value of a property
     *
     * @param string $name The name of the property
     * @return mixed The value of the property
     * @throws \InvalidArgumentException If the property doesn't exist
     */
    public function get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        throw new \InvalidArgumentException("Property '$name' does not exist in " . get_class($this));
    }

    /**
     * Sets the value of a property
     *
     * @param string $name The name of the property
     * @param mixed $value The value to set
     * @return self Returns the current instance for method chaining
     * @throws \InvalidArgumentException If the property doesn't exist
     */
    public function set(string $name, mixed $value): self
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
            return $this;
        }
        throw new \InvalidArgumentException("Property '$name' does not exist in " . get_class($this));
    }
} 