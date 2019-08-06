<?php


namespace App\Controller;

use App\Entity\Film;
use App\Exception\HttpException;
use App\Exception\ServiceException;
use App\Service\FilmService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class FilmController extends AbstractController
{
    private $filmService;
    private $logger;

    public function __construct(FilmService $filmService, LoggerInterface $logger)
    {
        $this->filmService = $filmService;
        $this->logger = $logger;
    }

    /**
     * @Route("/films", name="films_list", methods={"GET"})
     */
    public function list()
    {
        $films = $this->filmService->getRecommendedForUser($this->getUser());
        return $this->json($this->toApi($films));
    }

    /**
    * @Route("/film/{id}", name="film_view", methods={"GET"})
    */
    public function view(Film $film)
    {
        $filmData = $this->toApi([$film]);
        return $this->json($filmData[0]);
    }

    /**
     * @Route("/film", name="film_create", methods={"POST"})
     */
    public function create(Request $request)
    {
        $params = json_decode($request->getContent(), true);
        if (empty($params)) {
            throw new HttpException('Invalid json', HttpException::CODE_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $film = new Film();
        $film->setUser($this->getUser());
        $em->persist($film);

        try {
            $this->filmService->setFilmParams($film, $params);
        } catch (ServiceException $e) {
            throw new HttpException('Can not update film', 0, $e);
        }

        $em->flush();
        $data = $this->toApi([$film]);
        return $this->json($data[0]);
    }

    /**
     * @Route("/film/{id}", name="film_update", methods={"PUT"})
     */
    public function update(Film $film, Request $request)
    {
        $params = json_decode($request->getContent(), true);
        if (empty($params)) {
            throw new HttpException('Invalid json', HttpException::CODE_BAD_REQUEST);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($film);

        try {
            $this->filmService->setFilmParams($film, $params);
        } catch (ServiceException $e) {
            throw new HttpException('Can not update film', 0, $e);
        }

        $em->flush();
        $data = $this->toApi([$film]);
        return $this->json($data[0]);
    }


    /**
     * @param Film[] $films
     * @return array
     */
    private function toApi($films)
    {
        $params = [];

        foreach ($films as $film) {
            $params[] = [
                'id' => $film->getId(),
                'name' => $film->getName(),
                'description' => $film->getDescription(),
                'poster' => $this->getParameter('images_web_path') . '/' . $film->getPoster(),
                'genres' => $this->getItemsList($film->getGenres()),
                'companies' => $this->getItemsList($film->getCompanies()),
                'directors' => $this->getItemsList($film->getDirectors()),
                'actors' => $this->getItemsList($film->getActors()),
                'producers' => $this->getItemsList($film->getProducers()),
                'writers' => $this->getItemsList($film->getWriters()),
                'premiums' => $this->getItemsList($film->getPremiums()),
                'budget' => $film->getBudget(),
                'sales' => $film->getSales(),
                'languages' => $film->getLanguages(),
                'date' => $this->formatDate($film->getDate()),
                'duration' => $film->getDuration(),
            ];
        }

        return $params;
    }
}