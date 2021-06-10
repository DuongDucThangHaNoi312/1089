<?php

namespace App\Entity\SubscriptionPlan\Lookup;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubscriptionPlan\Lookup\RenewalFrequencyRepository")
 */
class RenewalFrequency
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Gedmo\Slug(handlers={
     *      @Gedmo\SlugHandler(class="Gedmo\Sluggable\Handler\InversedRelativeSlugHandler", options={
     *          @Gedmo\SlugHandlerOption(name="relationClass", value="App\Entity\SubscriptionPlan"),
     *          @Gedmo\SlugHandlerOption(name="mappedBy", value="renewalFrequency"),
     *          @Gedmo\SlugHandlerOption(name="inverseSlugField", value="slug")
     *      })
     * }, fields={"name"}, updatable=true)
     */
    private $slug;

    public function determineInterval() {
        $interval = 'month';

        switch ($this->getSlug()) {
            case 'monthly':
                return 'month';
            case 'annual':
                return 'year';
            case 'weekly':
                return 'week';
            case 'daily':
                return 'day';
        }
        return $interval;
    }

    public function __toString()
    {
        return (string) $this->getName();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}
