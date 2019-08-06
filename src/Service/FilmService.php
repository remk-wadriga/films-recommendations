<?php


namespace App\Service;

use App\Entity\Actor;
use App\Entity\Company;
use App\Entity\Director;
use App\Entity\Film;
use App\Entity\Genre;
use App\Entity\Premium;
use App\Entity\Producer;
use App\Entity\User;
use App\Entity\Writer;
use App\Exception\ServiceException;
use App\Helpers\File\FileCreatorFactory;

class FilmService extends AbstractService
{
    /**
     * @param User $user
     * @return Film[]
     */
    public function getRecommendedForUser(User $user)
    {
        $repository = $this->em->getRepository(Film::class);
        return $repository->findAll();
    }

    /**
     * @param Film $film
     * @param array $params
     *
     * @throws ServiceException
     */
    public function setFilmParams(Film $film, array $params)
    {
        $requiredParams = [
            'name' => 'string',
            'poster' => 'array',
            'genres' => 'array',
            'companies' => 'array',
            'directors' => 'array',
            'actors' => 'array',
            'producers' => 'array',
            'writers' => 'array',
            'premiums' => 'array',
            'budget' => 'integer',
            'sales' => 'integer',
            'languages' => 'array',
            'date' => 'string',
            'duration' => 'integer',
        ];

        // If this is updating - new poster is not required
        if (!isset($params['poster']) && $film->getId() !== null) {
            $params['poster'] = [
                'name' => $film->getPoster(),
                'data' => ''
            ];
        }

        // Filter params
        $errors = [];
        foreach ($requiredParams as $attr => $type) {
            if (!isset($params[$attr])) {
                $errors[] = sprintf('Param "%s" is required', $attr);
            } elseif (gettype($params[$attr]) !== $type) {
                $errors[] = sprintf('Param "%s" must have a type %s', $attr, $type);
            }
        }
        if (isset($params['description'])) {
            if (!is_string($params['description'])) {
                $errors[] = sprintf('Param "%s" must have a type %s', 'description', 'string');
            }
        } else {
            $params['description'] = null;
        }
        try {
            $params['date'] = new \DateTime($params['date']);
        } catch (\Exception $e) {
            $errors[] = sprintf('Invalid date format: %s', $e->getMessage());
        }
        if (!isset($params['poster']['name']) || !is_string($params['poster']['name']) || !isset($params['poster']['data']) || !is_string($params['poster']['data'])) {
            $errors[] = 'Param "poster" must be an array with string-params "name" and "data"';
        }
        if (!empty($errors)) {
            throw new ServiceException(implode('; ', $errors), ServiceException::CODE_INVALID_PARAMS);
        }

        // Flush film old related entities
        foreach ($film->getGenres() as $genre) {
            $film->removeGenre($genre);
        }
        foreach ($film->getCompanies() as $company) {
            $film->removeCompany($company);
        }
        foreach ($film->getDirectors() as $director) {
            $film->removeDirector($director);
        }
        foreach ($film->getActors() as $actor) {
            $film->removeActor($actor);
        }
        foreach ($film->getProducers() as $producer) {
            $film->removeProducer($producer);
        }
        foreach ($film->getWriters() as $writer) {
            $film->removeWriter($writer);
        }
        foreach ($film->getPremiums() as $premium) {
            $film->removePremium($premium);
        }

        // Set film attributes
        $film->setName($params['name']);
        $film->setDescription($params['description']);
        $film->setBudget($params['budget']);
        $film->setSales($params['sales']);
        $film->setLanguages($params['languages']);
        $film->setDate($params['date']);
        $film->setDuration($params['duration']);

        // Set film related entities
        foreach ($params['genres'] as $id) {
            $genre = $this->em->getRepository(Genre::class)->findOneById($id);
            if (empty($genre)) {
                $errors[] = sprintf('Genre #%s not found', $id);
                continue;
            }
            $film->addGenre($genre);
        }
        foreach ($params['companies'] as $id) {
            $company = $this->em->getRepository(Company::class)->findOneById($id);
            if (empty($company)) {
                $errors[] = sprintf('Company #%s not found', $id);
                continue;
            }
            $film->addCompany($company);
        }
        foreach ($params['directors'] as $id) {
            $director = $this->em->getRepository(Director::class)->findOneById($id);
            if (empty($director)) {
                $errors[] = sprintf('Director #%s not found', $id);
                continue;
            }
            $film->addDirector($director);
        }
        foreach ($params['actors'] as $id) {
            $actor = $this->em->getRepository(Actor::class)->findOneById($id);
            if (empty($actor)) {
                $errors[] = sprintf('Actor #%s not found', $id);
                continue;
            }
            $film->addActor($actor);
        }
        foreach ($params['producers'] as $id) {
            $producer = $this->em->getRepository(Producer::class)->findOneById($id);
            if (empty($producer)) {
                $errors[] = sprintf('Producer #%s not found', $id);
                continue;
            }
            $film->addProducer($producer);
        }
        foreach ($params['writers'] as $id) {
            $writer = $this->em->getRepository(Writer::class)->findOneById($id);
            if (empty($writer)) {
                $errors[] = sprintf('Writer #%s not found', $id);
                continue;
            }
            $film->addWriter($writer);
        }
        foreach ($params['premiums'] as $id) {
            $premium = $this->em->getRepository(Premium::class)->findOneById($id);
            if (empty($premium)) {
                $errors[] = sprintf('Premium #%s not found', $id);
                continue;
            }
            $film->addPremium($premium);
        }

        // To create the image files for film posters we need specified directory for them
        $filesDirectory = $this->getParam('images_dir');
        if ($filesDirectory === null) {
            throw new ServiceException('Config param "images_dir" is required to define the path for saving files', ServiceException::CODE_INVALID_CONFIG);
        }

        // Create film poster (if this is new film or it's updated)
        if ($film->getId() === null || $film->getPoster() != $params['poster']['name']) {
            $fileCreator = FileCreatorFactory::createReader($filesDirectory, $params['poster']['name'], $params['poster']['data']);
            $fileEntity = $fileCreator->create();
            $film->setPoster(basename($fileEntity->path));
        }
    }
}