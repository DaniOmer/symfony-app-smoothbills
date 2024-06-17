<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Company;
use App\Entity\Address;
use App\Entity\LegalForm;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        
        $industries = [
            'Technology',
            'Healthcare',
            'Finance',
            'Education',
            'Manufacturing',
            'Retail',
            'Construction',
            'Transportation',
            'Real Estate',
            'Energy'
        ];

        // Create Legal Forms
        $legalForms = [];
        for ($i = 0; $i < 3; $i++) {
            $legalForm = new LegalForm();
            $legalForm->setName($faker->companySuffix);
            $manager->persist($legalForm);
            $legalForms[] = $legalForm;
        }

        // Create Companies with unique addresses
        $companies = [];
        for ($i = 0; $i < 5; $i++) {
            // Create Address
            $address = new Address();
            $address->setZipcode($faker->postcode);
            $address->setCity($faker->city);
            $address->setCountry($faker->country);
            $address->setAddress($faker->streetAddress);
            $address->setLongitude($faker->longitude(-99.99999999, 99.99999999));
            $address->setLatitude($faker->latitude(-99.99999999, 99.99999999));
            $manager->persist($address);

            $company = new Company();
            $company->setDenomination($faker->company);
            $company->setSiren($faker->numerify('#########'));
            $company->setSiret($faker->numerify('##############'));
            $company->setTvaNumber($faker->numerify('FR###########'));
            $company->setRcsNumber($faker->numerify('RCS########'));
            $company->setPhoneNumber($faker->phoneNumber);
            $company->setMail($faker->companyEmail);
            $company->setCreationDate($faker->dateTimeBetween('-10 years', 'now'));
            $company->setRegisteredSocial($faker->numberBetween(1, 1000));
            $company->setSector($industries[array_rand($industries)]);
            $company->setLogo($faker->imageUrl(200, 200, 'business'));
            $company->setSigning($faker->imageUrl(200, 200, 'signature'));
            $company->setLegalForm($legalForms[array_rand($legalForms)]);
            $company->setAddress($address);
            $company->setUid(Uuid::v7());
            $manager->persist($company);
            $companies[] = $company;
        }

        $manager->flush();
    }
}
