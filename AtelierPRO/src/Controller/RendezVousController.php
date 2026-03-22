<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Repository\PatientRepository;
use App\Repository\RendezVousRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/rendez-vous')]
#[IsGranted('ROLE_ASSISTANTE_MEDICALE')]
class RendezVousController extends AbstractController
{
    /**
     * US01 — Étape 1 : Affiche l'écran de recherche (GET)
     *       Étape 2 : Retourne la liste des patients filtrés (POST/GET avec ?q=)
     */
    #[Route('/nouveau', name: 'rendez_vous_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        PatientRepository $patientRepository
    ): Response {
        $query    = $request->query->get('q', '');
        $patients = [];
        $searched = false;

        if ($query !== '') {
            $searched = true;
            $patients = $patientRepository->searchByNom($query);
        }

        return $this->render('rendez_vous/new.html.twig', [
            'query'    => $query,
            'patients' => $patients,
            'searched' => $searched,
        ]);
    }

    /**
     * US01 — Étape 3 : Enregistre le RDV après sélection du patient
     * Appelé via POST depuis la modale de confirmation.
     */
    #[Route('/creer', name: 'rendez_vous_create', methods: ['POST'])]
    public function create(
        Request $request,
        PatientRepository $patientRepository,
        EntityManagerInterface $em
    ): Response {
        // Protection CSRF
        if (!$this->isCsrfTokenValid('rdv_create', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide. Veuillez réessayer.');
            return $this->redirectToRoute('rendez_vous_new');
        }

        $patientId = (int) $request->request->get('patient_id');
        $dateStr   = $request->request->get('date');   // format : Y-m-d
        $heureStr  = $request->request->get('heure');  // format : H:i

        $patient = $patientRepository->find($patientId);

        if (!$patient) {
            $this->addFlash('error', 'Patient introuvable.');
            return $this->redirectToRoute('rendez_vous_new');
        }

        if (!$dateStr || !$heureStr) {
            $this->addFlash('error', 'Veuillez sélectionner une date et une heure.');
            return $this->redirectToRoute('rendez_vous_new', ['q' => $request->request->get('q')]);
        }

        try {
            $date  = new \DateTime($dateStr);
            $heure = new \DateTime($heureStr);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Format de date ou heure invalide.');
            return $this->redirectToRoute('rendez_vous_new');
        }

        $rdv = new RendezVous();
        $rdv->setPatient($patient);
        $rdv->setDate($date);
        $rdv->setHeure($heure);

        $em->persist($rdv);
        $em->flush();

        $this->addFlash('success', sprintf(
            'Rendez-vous créé pour %s %s le %s à %s.',
            $patient->getCivilite(),
            $patient->getNomComplet(),
            $date->format('d/m/Y'),
            $heure->format('H:i')
        ));

        return $this->redirectToRoute('rendez_vous_index');
    }

    /**
     * Liste de tous les RDV (accès tableau de bord).
     */
    #[Route('/', name: 'rendez_vous_index', methods: ['GET'])]
    public function index(RendezVousRepository $repo): Response
    {
        return $this->render('rendez_vous/index.html.twig', [
            'rendez_vous_list' => $repo->findAllOrderedByDate(),
        ]);
    }
}
