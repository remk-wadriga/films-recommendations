<?php


namespace App\Service;

use App\Entity\Actor;
use App\Entity\Company;
use App\Entity\Director;
use App\Entity\Film;
use App\Entity\Genre;
use App\Entity\Language;
use App\Entity\Premium;
use App\Entity\Producer;
use App\Entity\User;
use App\Entity\Writer;
use App\Exception\ServiceException;
use App\Helpers\File\FileCreatorFactory;
use App\Repository\FilmRepository;

class FilmService extends AbstractService
{
    /**
     * @param User $user
     * @param int $limit
     * @param int|null $offset
     *
     * @return Film[]
     */
    public function getRecommendedForUser(User $user, int $limit, int $offset = null)
    {
        /** @var FilmRepository $repository */
        $repository = $this->em->getRepository(Film::class);
        return $repository->findForPage($limit, $offset);
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
        $posterParamsError = 'Param "poster" must be an array with string-params "name" and "data"';
        foreach ($requiredParams as $attr => $type) {
            if (!array_key_exists($attr, $params)) {
                $errors[$attr] = sprintf('Param "%s" is required', $attr);
            } elseif (gettype($params[$attr]) !== $type) {
                $errors[$attr] = sprintf('Param "%s" must have a type %s', $attr, $type);
            }
        }
        if (isset($params['description'])) {
            if (!is_string($params['description'])) {
                $errors[] = sprintf('Param "%s" must have a type %s', 'description', 'string');
            }
        } else {
            $params['description'] = null;
        }
        if (!isset($errors['date'])) {
            try {
                $params['date'] = new \DateTime($params['date']);
            } catch (\Exception $e) {
                $errors[] = sprintf('Invalid date format: %s', $e->getMessage());
            }
        }
        if (!isset($params['poster']['name']) || !is_string($params['poster']['name']) || !isset($params['poster']['data']) || !is_string($params['poster']['data'])) {
            $errors[] = $posterParamsError;
        } elseif ($film->getId() === null && (empty($params['poster']['name']) || empty($params['poster']['data']))) {
            $errors[] = $posterParamsError;
        }
        if (isset($params['premiums']) && !is_array($params['premiums'])) {
            $errors[] = sprintf('Param "%s" must have a type %s', 'premiums', 'array');
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
        foreach ($film->getLanguages() as $language) {
            $film->removeLanguage($language);
        }

        // Set film attributes
        $film->setName($params['name']);
        $film->setDescription($params['description']);
        $film->setBudget($params['budget']);
        $film->setSales($params['sales']);
        $film->setDate($params['date']);
        $film->setDuration($params['duration']);
        if (!empty($params['slogan'])) {
            $film->setSlogan(mb_substr($params['slogan'], 0, 254));
        }
        if (!empty($params['rating'])) {
            $film->setRating(floatval($params['rating']));
        }

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
        if (isset($params['premiums'])) {
            foreach ($params['premiums'] as $id) {
                $premium = $this->em->getRepository(Premium::class)->findOneById($id);
                if (empty($premium)) {
                    $errors[] = sprintf('Premium #%s not found', $id);
                    continue;
                }
                $film->addPremium($premium);
            }
        }
        foreach ($params['languages'] as $id) {
            $language = $this->em->getRepository(Language::class)->findOneById($id);
            if (empty($language)) {
                $errors[] = sprintf('Language #%s not found', $id);
                continue;
            }
            $film->addLanguage($language);
        }

        // To create the image files for film posters we need specified directory for them
        $filesDirectory = $this->getParam('images_dir');
        if ($filesDirectory === null) {
            throw new ServiceException('Config param "images_dir" is required to define the path for saving files', ServiceException::CODE_INVALID_CONFIG);
        }

        // Create film poster (if this is new film or it's updated)
        if ($film->getId() === null || $film->getPoster() != $params['poster']['name']) {
            try {
                $fileCreator = FileCreatorFactory::createFileCreator($filesDirectory, $params['poster']['name'], $params['poster']['data']);
                $fileEntity = $fileCreator->create();
            } catch (\Exception $e) {
                throw new ServiceException(sprintf('Invalid poster image data: %s', $e->getMessage()), ServiceException::CODE_INVALID_PARAMS);
            }
            $film->setPoster(basename($fileEntity->path));
        }
    }
}