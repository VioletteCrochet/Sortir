<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\User;
use App\Entity\Event;
use App\Enum\EventStatus;
use App\Form\AddressType;
use App\Form\EventType;
use App\Form\FilterType;
use App\Repository\CampusRepository;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/event')]
final class EventController extends AbstractController
{
    #[Route('/', name: 'app_event_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EventRepository $eventRepository, EntityManagerInterface $entityManager, CampusRepository $campusRepository): Response
    {
        $form = $this->createForm(FilterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
            if (!empty($filters['campuses'])) {
                //dd($filters['campuses']);
                $events = $eventRepository->findByCampus($filters['campuses']->getId());
            } else {
                $events = $eventRepository->findAll();
            }
        } else {
            $events = $eventRepository->findAll();
        }

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'filterForm' => $form,
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {

        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté.e pour créer une sortie.');
        }

        $event = new Event();
        $eventForm = $this->createForm(EventType::class, $event);
        $eventForm->handleRequest($request);
        $address = new Address();
        $addressForm = $this->createForm(AddressType::class, $address);
        $addressForm->handleRequest($request);

        if ($eventForm->isSubmitted() && $eventForm->isValid() && $addressForm->isSubmitted() && $addressForm->isValid()) {

            $entityManager->persist($address);
            $event->setAddress($address);
            $event->setHost($this->getUser());
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }
        dump($event);

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'eventForm' => $eventForm,
            'addressForm' => $addressForm,
        ]);
    }

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $address = $event->getAddress() ?? new Address();

        $eventForm = $this->createForm(EventType::class, $event);
        $eventForm->handleRequest($request);
        $addressForm = $this->createForm(AddressType::class, $address);
        $addressForm->handleRequest($request);

        if ($eventForm->isSubmitted() && $eventForm->isValid() && $addressForm->isSubmitted() && $addressForm->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'eventForm' => $eventForm,
            'addressForm' => $addressForm,
        ]);
    }

    #[Route('/{id}/register', name: 'app_event_register', methods: ['GET','POST'])]
    public function register(Event $event, EntityManagerInterface $entityManager): Response
    {

        if ($event->getParticipants()->count() < $event->getMaxParticipantNumber() && $event->getHost() !== $this->getUser() && !($event->getParticipants()->contains($this->getUser())) && $event->getStatus() == EventStatus::OPENED)
        {
            $event->getParticipants()->add($this->getUser());
            $entityManager->persist($event);
            $entityManager->flush();
            $this->addFlash('success', 'Vous êtes bien inscrit.e pour la sortie ' . $event->getName());
        } else
        {
            $this->addFlash('error', 'Il n\'est pas possible de s\'inscrire à cette sortie');
        }
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/cancelRegistration', name: 'app_event_cancel_registration', methods: ['GET','POST'])]
    public function cancelRegistration(Event $event, EntityManagerInterface $entityManager): Response
    {

        if ($event->getParticipants()->contains($this->getUser()) && ($event->getStatus() == EventStatus::OPENED || $event->getStatus() == EventStatus::CLOSED))
        {
            $user = $this->getUser();

            foreach ($event->getParticipants() as $participant)
            {
                if ($participant->getId() == $this->getUser()->getId()) {
                    dump($participant->getId());
                    dump($this->getUser()->getId());
                    $event->removeParticipant($user);
                    $entityManager->persist($event);
                    $entityManager->flush();
                }
            }

            $this->addFlash('success', 'Vous êtes bien désinscrit.e pour la sortie ' . $event->getName());
        }

        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $eventName = $event->getName();
        $entityManager->remove($event);

    }




}
