<?php

namespace App\Form\Homepage;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class HomepageLinkSearchType
 * @package App\Form\Homepage
 */
class HomepageLinkSearchType extends HomepageJobSearchType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
            'hasJobTitles' => false
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'homepage_link_search';
    }
}
