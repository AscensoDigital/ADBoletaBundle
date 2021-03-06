<?php

namespace AscensoDigital\BoletaBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CargaResumenBoletasSiiFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('empresa', EntityType::class, [
            'class' => 'ADBoletaBundle:Empresa'
        ])
            ->add('file', FileType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AscensoDigital\BoletaBundle\Util\CargaResumenBoletasSii'
        ]);
    }

    public function getName()
    {
        return 'adboleta_carga_boleta_sii';
    }
}
