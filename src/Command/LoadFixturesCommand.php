<?php

namespace App\Command;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\User;
use App\Enum\BookFormat;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:load-fixtures',
    description: 'Charge les données de démonstration dans la base de données',
)]
class LoadFixturesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Chargement des fixtures SIYAG');

        // 1. Créer les utilisateurs
        $io->section('Création des utilisateurs');
        $admin = $this->createAdmin();
        $client = $this->createClient();
        $io->success('2 utilisateurs créés (admin@siyag.com / admin123 et client@test.com / client123)');

        // 2. Créer les auteurs
        $io->section('Création des auteurs');
        $authors = $this->createAuthors();
        $io->success(count($authors) . ' auteurs créés');

        // 3. Créer les livres
        $io->section('Création des livres');
        $books = $this->createBooks($authors);
        $io->success(count($books) . ' livres créés');

        // 4. Flush final
        $this->entityManager->flush();

        $io->success('Toutes les fixtures ont été chargées avec succès !');
        $io->note([
            'Compte admin : admin@siyag.com / admin123',
            'Compte client : client@test.com / client123',
            'Vous pouvez maintenant accéder à l\'application'
        ]);

        return Command::SUCCESS;
    }

    private function createAdmin(): User
    {
        $user = new User();
        $user->setEmail('admin@siyag.com');
        $user->setFirstname('Admin');
        $user->setLastname('SIYAG');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'admin123'));

        $this->entityManager->persist($user);

        return $user;
    }

    private function createClient(): User
    {
        $user = new User();
        $user->setEmail('client@test.com');
        $user->setFirstname('Jean');
        $user->setLastname('Dupont');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'client123'));

        $this->entityManager->persist($user);

        return $user;
    }

    private function createAuthors(): array
    {
        $authorsData = [
            [
                'name' => 'Victor Hugo',
                'bio' => 'Poète, dramaturge et prosateur romantique français. Considéré comme l\'un des plus importants écrivains de langue française.',
                'photo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e6/Victor_Hugo_by_%C3%89tienne_Carjat_1876_-_full.jpg/220px-Victor_Hugo_by_%C3%89tienne_Carjat_1876_-_full.jpg'
            ],
            [
                'name' => 'Émile Zola',
                'bio' => 'Écrivain et journaliste français, considéré comme le chef de file du naturalisme.',
                'photo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5a/%C3%89mile_Zola_2.jpg/220px-%C3%89mile_Zola_2.jpg'
            ],
            [
                'name' => 'Marcel Proust',
                'bio' => 'Écrivain français, auteur d\'À la recherche du temps perdu.',
                'photo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/09/Marcel_Proust_1900.jpg/220px-Marcel_Proust_1900.jpg'
            ],
            [
                'name' => 'Gustave Flaubert',
                'bio' => 'Romancier français, considéré comme l\'un des plus grands auteurs du XIXe siècle.',
                'photo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d1/Gustave_flaubert.jpg/220px-Gustave_flaubert.jpg'
            ],
            [
                'name' => 'Jules Verne',
                'bio' => 'Écrivain français, pionnier du roman d\'aventures et de science-fiction.',
                'photo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d5/F%C3%A9lix_Nadar_1820-1910_portraits_Jules_Verne_%28restoration%29.jpg/220px-F%C3%A9lix_Nadar_1820-1910_portraits_Jules_Verne_%28restoration%29.jpg'
            ],
            [
                'name' => 'Alexandre Dumas',
                'bio' => 'Écrivain français, auteur de romans d\'aventures célèbres.',
                'photo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/91/Alexandre_Dumas_p%C3%A8re_par_Nadar_-_Google_Art_Project_2.jpg/220px-Alexandre_Dumas_p%C3%A8re_par_Nadar_-_Google_Art_Project_2.jpg'
            ],
            [
                'name' => 'Honoré de Balzac',
                'bio' => 'Romancier français, auteur de La Comédie humaine.',
                'photo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0d/Honor%C3%A9_de_Balzac_%281842%29_Detail.jpg/220px-Honor%C3%A9_de_Balzac_%281842%29_Detail.jpg'
            ],
            [
                'name' => 'Albert Camus',
                'bio' => 'Écrivain, philosophe, romancier et nouvelliste français, prix Nobel de littérature.',
                'photo' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/08/Albert_Camus%2C_gagnant_de_prix_Nobel%2C_portrait_en_buste%2C_pos%C3%A9_au_bureau%2C_faisant_face_%C3%A0_gauche%2C_cigarette_de_tabagisme.jpg/220px-Albert_Camus%2C_gagnant_de_prix_Nobel%2C_portrait_en_buste%2C_pos%C3%A9_au_bureau%2C_faisant_face_%C3%A0_gauche%2C_cigarette_de_tabagisme.jpg'
            ],
        ];

        $authors = [];
        foreach ($authorsData as $data) {
            $author = new Author();
            $author->setName($data['name']);
            $author->setBio($data['bio']);
            $author->setPhoto($data['photo']);

            $this->entityManager->persist($author);
            $authors[$data['name']] = $author;
        }

        return $authors;
    }

    private function createBooks(array $authors): array
    {
        $booksData = [
            [
                'title' => 'Les Misérables',
                'isbn' => '978-2070409228',
                'description' => 'Fresque sociale et historique qui dépeint la vie des miséreux dans Paris et la France provinciale du XIXe siècle.',
                'cover' => 'https://m.media-amazon.com/images/I/51t4gN1RKLL._SX331_BO1,204,203,200_.jpg',
                'price' => 1599,
                'format' => BookFormat::PHYSICAL,
                'stock' => 10,
                'published' => '1862-04-03',
                'authors' => ['Victor Hugo']
            ],
            [
                'title' => 'Notre-Dame de Paris',
                'isbn' => '978-2070413089',
                'description' => 'Roman historique qui se déroule à Paris au XVe siècle, mettant en scène Quasimodo et Esmeralda.',
                'cover' => 'https://m.media-amazon.com/images/I/51mZx7YIKOL._SX331_BO1,204,203,200_.jpg',
                'price' => 1299,
                'format' => BookFormat::PHYSICAL,
                'stock' => 15,
                'published' => '1831-03-16',
                'authors' => ['Victor Hugo']
            ],
            [
                'title' => 'Germinal',
                'isbn' => '978-2070360338',
                'description' => 'Roman sur la condition des mineurs du Nord de la France au XIXe siècle.',
                'cover' => 'https://m.media-amazon.com/images/I/51TG6Y5YZHL._SX331_BO1,204,203,200_.jpg',
                'price' => 999,
                'format' => BookFormat::DIGITAL,
                'stock' => null,
                'published' => '1885-03-13',
                'authors' => ['Émile Zola']
            ],
            [
                'title' => 'L\'Assommoir',
                'isbn' => '978-2070368976',
                'description' => 'Roman naturaliste sur l\'alcoolisme dans les milieux populaires parisiens.',
                'cover' => 'https://m.media-amazon.com/images/I/51HCl5AzXrL._SX331_BO1,204,203,200_.jpg',
                'price' => 899,
                'format' => BookFormat::DIGITAL,
                'stock' => null,
                'published' => '1877-01-01',
                'authors' => ['Émile Zola']
            ],
            [
                'title' => 'Du côté de chez Swann',
                'isbn' => '978-2070364701',
                'description' => 'Premier tome d\'À la recherche du temps perdu.',
                'cover' => 'https://m.media-amazon.com/images/I/51SfiFuYmFL._SX331_BO1,204,203,200_.jpg',
                'price' => 1499,
                'format' => BookFormat::PHYSICAL,
                'stock' => 8,
                'published' => '1913-11-14',
                'authors' => ['Marcel Proust']
            ],
            [
                'title' => 'Madame Bovary',
                'isbn' => '978-2070413119',
                'description' => 'Roman sur l\'ennui et l\'adultère d\'une femme de médecin de province.',
                'cover' => 'https://m.media-amazon.com/images/I/51mfWM+vOTL._SX331_BO1,204,203,200_.jpg',
                'price' => 1199,
                'format' => BookFormat::PHYSICAL,
                'stock' => 12,
                'published' => '1857-04-01',
                'authors' => ['Gustave Flaubert']
            ],
            [
                'title' => 'L\'Éducation sentimentale',
                'isbn' => '978-2070368679',
                'description' => 'Roman qui dépeint la vie de la jeunesse parisienne sous la monarchie de Juillet.',
                'cover' => 'https://m.media-amazon.com/images/I/51UfOZLxdnL._SX331_BO1,204,203,200_.jpg',
                'price' => 1299,
                'format' => BookFormat::PHYSICAL,
                'stock' => 7,
                'published' => '1869-11-17',
                'authors' => ['Gustave Flaubert']
            ],
            [
                'title' => 'Vingt mille lieues sous les mers',
                'isbn' => '978-2253006329',
                'description' => 'Roman d\'aventures sous-marines avec le capitaine Nemo et le Nautilus.',
                'cover' => 'https://m.media-amazon.com/images/I/51kSvR-jTkL._SX331_BO1,204,203,200_.jpg',
                'price' => 999,
                'format' => BookFormat::PHYSICAL,
                'stock' => 20,
                'published' => '1869-11-28',
                'authors' => ['Jules Verne']
            ],
            [
                'title' => 'Le Tour du monde en quatre-vingts jours',
                'isbn' => '978-2253006336',
                'description' => 'Les aventures de Phileas Fogg dans son pari autour du monde.',
                'cover' => 'https://m.media-amazon.com/images/I/51JQ7H7+7bL._SX331_BO1,204,203,200_.jpg',
                'price' => 899,
                'format' => BookFormat::DIGITAL,
                'stock' => null,
                'published' => '1873-01-31',
                'authors' => ['Jules Verne']
            ],
            [
                'title' => 'Les Trois Mousquetaires',
                'isbn' => '978-2253098232',
                'description' => 'Les aventures de d\'Artagnan et ses trois amis mousquetaires.',
                'cover' => 'https://m.media-amazon.com/images/I/51j0Q3RJMIL._SX331_BO1,204,203,200_.jpg',
                'price' => 1499,
                'format' => BookFormat::PHYSICAL,
                'stock' => 15,
                'published' => '1844-07-01',
                'authors' => ['Alexandre Dumas']
            ],
            [
                'title' => 'Le Comte de Monte-Cristo',
                'isbn' => '978-2253098232',
                'description' => 'Histoire de vengeance d\'Edmond Dantès après son emprisonnement injuste.',
                'cover' => 'https://m.media-amazon.com/images/I/51vH7xRh7oL._SX331_BO1,204,203,200_.jpg',
                'price' => 1699,
                'format' => BookFormat::PHYSICAL,
                'stock' => 10,
                'published' => '1844-08-28',
                'authors' => ['Alexandre Dumas']
            ],
            [
                'title' => 'Le Père Goriot',
                'isbn' => '978-2070369119',
                'description' => 'Roman social sur un père dévoué ruiné par ses filles ingrates.',
                'cover' => 'https://m.media-amazon.com/images/I/51TfGQkH5JL._SX331_BO1,204,203,200_.jpg',
                'price' => 1099,
                'format' => BookFormat::PHYSICAL,
                'stock' => 9,
                'published' => '1835-03-01',
                'authors' => ['Honoré de Balzac']
            ],
            [
                'title' => 'Eugénie Grandet',
                'isbn' => '978-2253004905',
                'description' => 'Roman sur l\'avarice et ses conséquences sur une famille.',
                'cover' => 'https://m.media-amazon.com/images/I/51xXkHLNwJL._SX331_BO1,204,203,200_.jpg',
                'price' => 999,
                'format' => BookFormat::DIGITAL,
                'stock' => null,
                'published' => '1833-09-19',
                'authors' => ['Honoré de Balzac']
            ],
            [
                'title' => 'L\'Étranger',
                'isbn' => '978-2070360024',
                'description' => 'Roman philosophique sur l\'absurdité de l\'existence.',
                'cover' => 'https://m.media-amazon.com/images/I/41Y5tF6WQSL._SX331_BO1,204,203,200_.jpg',
                'price' => 899,
                'format' => BookFormat::DIGITAL,
                'stock' => null,
                'published' => '1942-06-01',
                'authors' => ['Albert Camus']
            ],
            [
                'title' => 'La Peste',
                'isbn' => '978-2070360420',
                'description' => 'Roman allégorique sur une épidémie de peste dans la ville d\'Oran.',
                'cover' => 'https://m.media-amazon.com/images/I/41p+W2YQFnL._SX331_BO1,204,203,200_.jpg',
                'price' => 999,
                'format' => BookFormat::DIGITAL,
                'stock' => null,
                'published' => '1947-06-10',
                'authors' => ['Albert Camus']
            ],
        ];

        $books = [];
        foreach ($booksData as $data) {
            $book = new Book();
            $book->setTitle($data['title']);
            $book->setIsbn($data['isbn']);
            $book->setDescription($data['description']);
            $book->setCoverImage($data['cover']);
            $book->setPriceCents($data['price']);
            $book->setFormat($data['format']);
            $book->setStock($data['stock']);
            $book->setIsActive(true);
            $book->setPublishedAt(new \DateTimeImmutable($data['published']));

            // Associer les auteurs
            foreach ($data['authors'] as $authorName) {
                if (isset($authors[$authorName])) {
                    $book->addAuthor($authors[$authorName]);
                }
            }

            $this->entityManager->persist($book);
            $books[] = $book;
        }

        return $books;
    }
}
