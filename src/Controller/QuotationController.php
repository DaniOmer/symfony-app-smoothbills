<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Company;
use App\Entity\Customer;
use App\Entity\LegalForm;
use App\Entity\Quotation;
use App\Entity\QuotationStatus;
use App\Form\QuotationType;
use App\Repository\QuotationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard/quotation')]
class QuotationController extends AbstractController
{
    #[Route('/', name: 'dashboard.quotation.index', methods: ['GET'])]
    public function index(QuotationRepository $quotationRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $paginateQuotations = $quotationRepository->paginateQuotations($page);

        $headers = ['Nom', 'Prix', 'Status', 'Vendeur', 'Envoyé le'];
        $rows = [];

        foreach ($paginateQuotations as $quotation) {
            $rows[] = [
                'name' => $quotation->getQuotationHasServices()->getService()->getName(),
                'price' => $quotation->getQuotationHasServices()->getPriceWithoutTax(),
                'status' => $quotation->getType(),
                'vendor' => $quotation->getQuotationHasServices()->getService()->getCompany(),
                'date' => $quotation->getDate(),
            ];
        }

        return $this->render('dashboard/quotation/index.html.twig', [
            'headers' => $headers,
            'rows' => $rows,
            'quotations' => $paginateQuotations,
        ]);
    }

    #[Route('/new', name: 'dashboard.quotation.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $quotation = new Quotation();
        $quotationStatus = new QuotationStatus;
        $company = new Company();
        $legalForm = new LegalForm();
        $companyAddress = new Address();
        $customer = new Customer();
        $customerAddress = new Address();
        $form = $this->createForm(QuotationType::class, $quotation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quotationStatus->setName($form->get('quotation_status')->getData()->getName());
            $entityManager->persist($quotationStatus);

            $companyData = $form->get('company')->getData();
            $company->setDenomination($companyData->getDenomination());
            $company->setSiren($companyData->getSiren());
            $company->setSiret($companyData->getSiret());
            $company->setTvaNumber($companyData->getTvaNumber());
            $company->setRcsNumber($companyData->getRcsNumber());
            $company->setPhoneNumber($companyData->getPhoneNumber());
            $company->setMail($companyData->getMail());
            $company->setCreationDate($companyData->getCreationDate());
            $company->setRegisteredSocial($companyData->getRegisteredSocial());
            $company->setSector($companyData->getSector());
            $company->setLogo($companyData->getLogo());
            $company->setSigning($companyData->getSigning());
            
            $legalFormData = $companyData->getLegalForm();
            $legalForm->setName($legalFormData->getName());
            $entityManager->persist($legalForm);

            $company->setLegalForm($legalForm);

            $companyAddressData = $form->get('company')->get('address')->getData();
            $companyAddress->setZipcode($companyAddressData->getZipcode());
            $companyAddress->setCity($companyAddressData->getCity());
            $companyAddress->setCountry($companyAddressData->getCountry());
            $companyAddress->setAddress($companyAddressData->getAddress());
            $entityManager->persist($companyAddress);

            $company->setAddress($companyAddress);
            $entityManager->persist($company);

            $customerData = $form->get('customer')->getData();
            $customer->setName($customerData->getName());
            $customer->setMail($customerData->getMail());
            $customer->setPhone($customerData->getPhone());
            $customer->setType($customerData->getType());
            $customer->setCompany($company);
            $customer->setCreatedBy($this->getUser());

            $customerAddressData = $customerData->getAddress();
            $customerAddress->setZipcode($customerAddressData->getZipcode());
            $customerAddress->setCity($customerAddressData->getCity());
            $customerAddress->setCountry($customerAddressData->getCountry());
            $customerAddress->setAddress($customerAddressData->getAddress());
            $entityManager->persist($customerAddress);

            $customer->setAddress($customerAddress);
            $entityManager->persist($customer);

            $quotation->setQuotationStatus($quotationStatus);
            $quotation->setCompany($company);
            $quotation->setCustomer($customer);
            $entityManager->persist($quotation);

            $entityManager->flush();

            return $this->redirectToRoute('dashboard.quotation.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/quotation/new.html.twig', [
            'quotation' => $quotation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'dashboard.quotation.show', methods: ['GET'])]
    public function show(Quotation $quotation): Response
    {
        return $this->render('dashboard/quotation/show.html.twig', [
            'quotation' => $quotation,
        ]);
    }

    #[Route('/{id}/edit', name: 'dashboard.quotation.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Quotation $quotation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(QuotationType::class, $quotation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('dashboard/dashboard.quotation.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/quotation/edit.html.twig', [
            'quotation' => $quotation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'dashboard.quotation.delete', methods: ['POST'])]
    public function delete(Request $request, Quotation $quotation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$quotation->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($quotation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('dashboard/dashboard.quotation.index', [], Response::HTTP_SEE_OTHER);
    }
}