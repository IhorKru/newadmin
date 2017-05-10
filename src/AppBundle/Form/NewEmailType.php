<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewEmailType extends AbstractType{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)  {
        
        $builder
            ->add('app', EntityType::class, [
                'label' => false,
                'required' => true,
                'error_bubbling' => true,
                'placeholder' => 'Select Resource',
                'class' => 'AppBundle:SendyApps',
                'choice_label' => 'app_name',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('template_name', TextType::class, [
                'label' => false,
                'required' => true,
                'error_bubbling' => true,
                'attr' => [
                    'placeholder' => 'Template Name',
                    'class' => 'form-control'
                ]])
            ->add('htmltext', FileType::class, [
                'label' => false,
                'required' => true,
                'error_bubbling' => true,
                'attr' => [
                    'action' => 'newemailtempl',
                    'class' => 'form-control'
                ]])
            ->add('submit', SubmitType::class, [
                'label' => 'Create Template',
                'attr' => [
                    'class' => 'btn btn-success btn-block'
                ]])
        ;
    }
    
    /**
    * @param OptionsResolverInterface $resolver
    */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\Template'
        ]);
    }
    /**
     * @return string
     */
    public function getName() {
        return 'newemail';
    }
}
