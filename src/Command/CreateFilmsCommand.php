<?php

namespace App\Command;


use App\Entity\Actor;
use App\Entity\Company;
use App\Entity\Country;
use App\Entity\Director;
use App\Entity\Film;
use App\Entity\Genre;
use App\Entity\Language;
use App\Entity\Producer;
use App\Entity\Types\Enum\GenderEnum;
use App\Entity\User;
use App\Entity\Writer;
use App\Exception\ServiceException;
use App\Helpers\File\FileCreatorFactory;
use App\Helpers\StringHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateFilmsCommand extends AbstractCommand
{
    protected static $defaultName = 'app:create-films';

    protected $filmsPath = 'kinopoisk_pages';
    protected $filmsPages;

    protected $companiesPath = 'kinopoisk_companies_pages';
    protected $companiesPages;

    protected $filmsCacheFile = 'films/films_info.cache';
    protected $languagesCodesFile = 'languages_codes.cache';
    protected $countriesCodesFile = 'countries_codes.cache';

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription(sprintf('Creates the films using site "kinopoisk.ru".'))
            // the full command description shown when running the command with the "--help" option
            ->setHelp('This command allows you to create the films using site "kinopoisk.ru".')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $films = $this->getFileReader($this->filmsCacheFile)->readFile();
        if (empty($films)) {
            $films = $this->parseFilmsFromPages($input, $output);
            $this->getFileReader($this->filmsCacheFile)->writeData($films);
        }
        $languagesCodes = $this->getFileReader($this->languagesCodesFile)->readFile();
        $countriesCodes = $this->getFileReader($this->countriesCodesFile)->readFile();

        $output->writeln('Start writing films to DB');

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneById(1);
        foreach ($films as $film) {
            $output->writeln(sprintf('Processing film "%s"...', $film['name']));

            $filmModel = new Film();
            $filmModel
                ->setUser($user)
                ->setName($film['name'])
                ->setPoster($film['poster'])
                ->setDate(new \DateTime(isset($film['date']) ? $film['date'] . '-01-01' : 'now'))
                ->setBudget(intval($film['budget']))
                ->setSales(intval($film['sales']))
                ->setDuration($film['duration'] * 60)
                ->setDescription($film['description'])
                ->setSlogan($film['slogan'])
                ->setRating(floatval($film['rating']))
            ;

            /** @var Country[] $countries */
            $countries = [];
            foreach ($film['countries'] as $name) {
                /** @var Country $country */
                $country = $this->em->getRepository(Country::class)->findOneByName($name);
                if ($country === null) {
                    $country = $this->setEntityAttributes(new Country(), ['name' => $name, 'code' => $countriesCodes[$name]]);
                    $this->em->persist($country);
                }
                $countries[] = $country;
            }

            /** @var Company[] $companies */
            $companies = [];
            $countryIndex = 0;
            foreach ($film['companies'] as $name) {
                $company = $this->em->getRepository(Company::class)->findOneByName($name);
                if ($company === null) {
                    /** @var Company $company */
                    $company = $this->setEntityAttributes(new Company(), ['name' => $name, 'staff' => 0]);
                    if (!isset($countries[$countryIndex])) {
                        $countryIndex = 0;
                    }
                    $country = isset($countries[$countryIndex]) ? $countries[$countryIndex++] : $this->em->getRepository(Country::class)->findOneById(1);
                    $company->setCountry($country);
                    $this->em->persist($company);
                }
                $companies[] = $company;
                $filmModel->addCompany($company);
            }

            /** @var Director[] $countries */
            $directors = [];
            $countryIndex = 0;
            foreach ($film['directors'] as $name) {
                /** @var Director $director */
                $director = $this->em->getRepository(Director::class)->findOneByName($name);
                if ($director === null) {
                    $director = $this->setEntityAttributes(new Director(), ['name' => $name, 'age' => 45, 'sex' => GenderEnum::TYPE_MALE]);
                    $this->em->persist($director);
                }
                if (!isset($countries[$countryIndex])) {
                    $countryIndex = 0;
                }
                $country = isset($countries[$countryIndex]) ? $countries[$countryIndex++] : $this->em->getRepository(Country::class)->findOneById(1);
                $director->setCountry($country);
                $directors[] = $director;
                $filmModel->addDirector($director);
            }

            /** @var Writer[] $countries */
            $writers = [];
            $countryIndex = 0;
            foreach ($film['writers'] as $name) {
                /** @var Writer $writer */
                $writer = $this->em->getRepository(Writer::class)->findOneByName($name);
                if ($writer === null) {
                    $writer = $this->setEntityAttributes(new Writer(), ['name' => $name, 'age' => 45, 'sex' => GenderEnum::TYPE_MALE]);
                    $this->em->persist($writer);
                }
                if (!isset($countries[$countryIndex])) {
                    $countryIndex = 0;
                }
                $country = isset($countries[$countryIndex]) ? $countries[$countryIndex++] : $this->em->getRepository(Country::class)->findOneById(1);
                $writer->setCountry($country);
                $writers[] = $writer;
                $filmModel->addWriter($writer);
            }

            /** @var Producer[] $countries */
            $producers = [];
            $companyIndex = 0;
            foreach ($film['producers'] as $name) {
                /** @var Producer $producer */
                $producer = $this->em->getRepository(Producer::class)->findOneByName($name);
                if ($producer === null) {
                    $producer = $this->setEntityAttributes(new Producer(), ['name' => $name, 'age' => 45, 'sex' => GenderEnum::TYPE_MALE]);
                    $this->em->persist($producer);
                }
                if (!isset($companies[$companyIndex])) {
                    $companyIndex = 0;
                }
                $producer->setCompany($companies[$companyIndex++]);
                $producers[] = $producer;
                $filmModel->addProducer($producer);
            }

            /** @var Genre $genres */
            $genres = [];
            foreach ($film['genres'] as $name) {
                $genre = $this->em->getRepository(Genre::class)->findOneByName($name);
                if ($genre === null) {
                    $genre = $this->setEntityAttributes(new Genre(), ['name' => $name]);
                    $this->em->persist($genre);
                }
                $genres[] = $genre;
                $filmModel->addGenre($genre);
            }

            /** @var Actor[] $countries */
            $actors = [];
            $countryIndex = 0;
            foreach ($film['actors'] as $name) {
                /** @var Actor $actor */
                $actor = $this->em->getRepository(Actor::class)->findOneByName($name);
                if ($actor === null) {
                    $actor = $this->setEntityAttributes(new Actor(), ['name' => $name, 'age' => 45, 'sex' => GenderEnum::TYPE_MALE]);
                    $this->em->persist($actor);
                }
                if (!isset($countries[$countryIndex])) {
                    $countryIndex = 0;
                }
                $country = isset($countries[$countryIndex]) ? $countries[$countryIndex++] : $this->em->getRepository(Country::class)->findOneById(1);
                $actor->setCountry($country);
                $actors[] = $actor;
                $filmModel->addActor($actor);
            }

            /** @var Language[] $languages */
            $languages = [];
            foreach ($film['languages'] as $name) {
                /** @var Language $language */
                $language = $this->em->getRepository(Language::class)->findOneByName($name);
                if ($language === null) {
                    $language = $this->setEntityAttributes(new Language(), ['name' => $name, 'code' => $languagesCodes[$name]]);
                    $this->em->persist($language);
                }
                $languages[] = $language;
                $filmModel->addLanguage($language);
            }

            $this->em->persist($filmModel);
        }
        $this->em->flush();
        $output->writeln(sprintf('Done. %s films created.', count($films)));
    }


    private function parseFilmsFromPages(InputInterface $input, OutputInterface $output): array
    {
        $this->filmsPath = $this->filesDir . DIRECTORY_SEPARATOR . $this->filmsPath;
        $this->companiesPath = $this->filesDir . DIRECTORY_SEPARATOR . $this->companiesPath;
        $output->writeln('Start reading films pages');
        $films = [];

        foreach ($this->getFilmsPages() as $page) {
            /*if (strpos($page, 'Операция Мертвый снег') === false) {
                continue;
            }*/

            $pageParts = explode(' — ', $page);
            $companiesPage = $this->getFilmCompaniesPage(current($pageParts));
            if (empty($companiesPage)) {
                throw new ServiceException(sprintf('Companies page for page "%s" is not found', $page), ServiceException::CODE_INVALID_CONFIG);
            }
            $companiesWebReader = $this->getWebReader(null, [
                'explodePattern' => '<\/table>',
                'searchPatterns' => [
                    'languages' => ['<b>Язык:<\/b>', '<td style="color:#333; text-align: left">([^\d+].+)<\/td>', null, null, null, true],
                    'companies' => ['<b>Производство:<\/b>', '<a.*>(.+)<\/a>', '&nbsp;', ' ', null, true],
                ],
            ], false);
            $output->writeln(sprintf('Read companies page "%s"', $companiesPage));
            $companiesData = $companiesWebReader->read($this->companiesPath . DIRECTORY_SEPARATOR . $companiesPage);
            if (empty($companiesData) || empty($companiesData['companies'])) {
                throw new ServiceException(sprintf('Companies for page "%s" are not found', $page), ServiceException::CODE_INVALID_CONFIG);
            }
            $companies = array_filter(array_map(function ($company) {
                if (mb_strpos($company, 'География съемок') !== false) {
                    return null;
                }
                $company = str_replace(['+', '[', ']'], '', $company);
                return trim(StringHelper::mb_ucfirst($company));
            }, $companiesData['companies']));
            if (empty($companies)) {
                throw new ServiceException(sprintf('Companies for page "%s" are not found', $page), ServiceException::CODE_INVALID_CONFIG);
            }

            $languages = !empty($companiesData['languages']) ? explode(', ', end($companiesData['languages'])) : ['английский'];
            $languages = array_map(function ($lang) { return trim(StringHelper::mb_ucfirst($lang)); }, $languages);

            $filmWebReader = $this->getWebReader(null, [
                'explodePattern' => '\n|<\/tr>',
                'searchPatterns' => [
                    'poster' => ['<a.*class="popupBigImage".*>', '<img.*src="(.+)"', './', $this->filmsPath . DIRECTORY_SEPARATOR, null, false],
                    'name' => ['<h1 class="moviename-big".*>', '<span.*>(.+)<\/span>', '&nbsp;', ' ', null, false],
                    'date' => ['<td.*class="type".*>год', '<a.*>(\d+)<\/a>', null, null, null, false],
                    'countries' => ['<td.*class="type".*>страна<\/td>', '<a.*>([^\.].+)<\/a>', null, null, null, true],
                    'slogan' => ['<td.*class="type".*>слоган', '<td.*>«(.+)»<\/td>', null, null, null, false],
                    'directors' => ['<td.*class="type".*>режиссер<\/td>', '<a.*>([^\.].+)<\/a>', null, null, null, true],
                    'writers' => ['<td.*class="type".*>сценарий<\/td>', '<a.*>([^\.].+)<\/a>', null, null, null, true],
                    'producers' => ['<td.*class="type".*>продюсер<\/td>', '<a.*>([^\.].+)<\/a>', null, null, null, true],
                    'genres' => ['<td.*class="type".*>жанр<\/td>', '<a.*>([^\.][^<].+)<\/a>', null, null, null, true],
                    'budget' => ['<td.*class="type".*>бюджет', '<a.*>(.+)<\/a>', ['$', '&nbsp;'], '', null, false],
                    'sales' => ['<td.*class="type".*>сборы в мире', '<a.*>.+=(.+)<\/a>', ['$', '&nbsp;'], '', null, false],
                    'duration' => ['<td.*class="type".*>время', '<td class="time".*>(\d+) мин\..+<\/td>', null, null, null, false],
                    'actors' => ['<h4>В главных ролях:<\/h4>', '<a.*>([^\.].+)<\/a>', null, null, null, true],
                    'description' => ['<span class="_reachbanner_".*', '<div class=".*film-synopsys.*".*>(.+)<\/div>', '&nbsp;', ' ', null, false],
                    'rating' => ['<div class="rating_title".*', '<span class="rating_ball">([\d+.]+)<\/span>', null, null, null, false],
                ],
            ], false);

            $output->writeln(sprintf('Read film page "%s"', $page));
            $data = $filmWebReader->read($this->filmsPath . DIRECTORY_SEPARATOR . $page);
            if (empty($data) || empty($data['name'])) {
                $output->writeln('Failed');
                continue;
            }
            if (!empty($data['genres']) && in_array('слова', $data['genres'])) {
                unset($data['genres'][array_search('слова', $data['genres'])]);
            }
            if (!empty($data['genres'])) {
                $data['genres'] = array_map(function ($genre) { return trim(StringHelper::mb_ucfirst($genre)); }, $data['genres']);
            }

            $output->writeln('Successful!');

            if (!empty($data['poster'])) {
                $filesDirectory = $this->getParam('images_dir');
                $fileCreator = FileCreatorFactory::createFileCreator($filesDirectory, basename($data['poster']), base64_encode(file_get_contents($data['poster'])));
                $fileEntity = $fileCreator->create();
                $data['poster'] = basename($fileEntity->path);
            }
            $data['languages'] = $languages;
            $data['companies'] = $companies;
            if (!isset($data['poster'])) {
                $data['poster'] = 'no_poster.png';
            }
            if (!isset($data['date'])) {
                $data['date'] = null;
            }
            if (!isset($data['slogan'])) {
                $data['slogan'] = null;
            }
            if (!isset($data['directors'])) {
                $data['directors'] = [];
            }
            if (!isset($data['writers'])) {
                $data['writers'] = [];
            }
            if (!isset($data['producers'])) {
                $data['producers'] = [];
            }
            if (!isset($data['genres'])) {
                $data['genres'] = [];
            }
            if (!isset($data['budget'])) {
                $data['budget'] = 0;
            }
            if (!isset($data['sales'])) {
                $data['sales'] = 0;
            }
            if (!isset($data['duration'])) {
                $data['duration'] = 0;
            }
            if (!isset($data['actors'])) {
                $data['actors'] = [];
            }
            if (!isset($data['description'])) {
                $data['description'] = null;
            }
            if (!isset($data['rating'])) {
                $data['rating'] = 0;
            }

            if (!empty($data['slogan'])) {
                $data['slogan'] = trim(StringHelper::mb_ucfirst($data['slogan']));
            }

            $data['name'] = trim(str_replace(['«', '»', ',', ':'], '', $data['name']));
            $data['name'] = StringHelper::mb_ucfirst($data['name']);
            $nameWithDate = $data['name'];
            if (isset($data['date'])) {
                $nameWithDate .= ' ' . $data['date'];
            }
            $films[$nameWithDate] = $data;
        }

        return $films;
    }

    private function getFilmCompaniesPage(string $filmName): ?string
    {
        $filmName = trim(mb_strtolower($filmName));
        foreach ($this->getCompaniesPages() as $i => $page) {
            $pageParts = explode(' — ', $page);
            $pageName = trim(mb_strtolower(current($pageParts)));
            if ($filmName == $pageName) {
                return $page;
            }
        }
        return null;
    }

    private function getFilmsPages(): array
    {
        if ($this->filmsPages !== null) {
            return $this->filmsPages;
        }
        return $this->filmsPages = $this->getAllDirectories($this->filmsPath);
    }

    private function getCompaniesPages()
    {
        if ($this->companiesPages !== null) {
            return $this->companiesPages;
        }
        return $this->companiesPages = $this->getAllDirectories($this->companiesPath);
    }

    private function getAllDirectories(string $baseDir): array
    {
        if (!is_dir($baseDir)) {
            throw new ServiceException(sprintf('The directory %s does not exist!', $baseDir), ServiceException::CODE_INVALID_CONFIG);
        }
        $directories = [];
        foreach (scandir($baseDir) as $dirName) {
            if (in_array($dirName, ['.', '..']) || is_dir($baseDir . DIRECTORY_SEPARATOR . $dirName)) {
                continue;
            }
            $directories[] = $dirName;
        }
        return $directories;
    }

    private function setEntityAttributes($entity, $attributes)
    {
        foreach ($attributes as $name => $value) {
            $setter = 'set' . ucfirst($name);
            if (method_exists($entity, $setter)) {
                $entity->$setter($value);
            }
        }
        return $entity;
    }
}