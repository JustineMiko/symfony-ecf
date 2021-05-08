<?php

namespace App\DataFixtures;

use App\Entity\Project;
use App\Entity\SchoolYear;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{


    private $encoder;
    private $manager;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {

        // créer un générateur de fausses données, localisé pour le français
        $this->faker = \Faker\Factory::create('fr_FR');  

        $this->manager = $manager;

        // @todo créé un faux utilisateur sans aucun privilège mais avec l'id 1

        // créer un user ROLE_ADMIN
        $user = new User();
        $firstname = 'Foo';
        $lastname = 'Foo';
        $email = 'foo.foo@example.com';
        $roles = ["ROLE_ADMIN"];
        $password = $this->encoder->encodePassword($user, '123');        
        $phone = null;
        $user->setFirstname($firstname)
            ->setLastname($lastname)
            ->setEmail($email)
            ->setPhone($phone)
            ->setRoles($roles)
            ->setPassword($password);

        $this->manager->persist($user);
        $this->manager->flush();

        
        $this->loadUser(60, "ROLE_STUDENT");
        $this->loadUser(5, "ROLE_TEACHER");
        $this->loadUser(15, "ROLE_CLIENT");

        $this->loadProject(20);
        $this->loadSchoolYear(3);
        $this->loadUserSchoolYearRelation(3);

    }

    public function loadUser(int $userCount, string $userRole) : void 
    {


        for ($i = 0; $i < 60; $i++) {
            $user = new User();
            $firstname = $this->faker->firstname();
            $lastname = $this->faker->lastname();
            $email = strtolower($firstname).'.'.strtolower($lastname). '-'.$i.'@example.com';
            $roles = [$userRole];
            $password = $this->encoder->encodePassword($user, strtolower($firstname));
            
            $phone = $this->faker->phoneNumber();
            
            $user = $user->setFirstname($firstname)
                ->setLastname($lastname)
                ->setEmail($email)
                ->setPhone($phone)
                ->setRoles($roles)
                ->setPassword($password);

            $this->manager->persist($user);
        }

        $this->manager->flush();

    }

    public function loadProject(int $count) : void {

        for ($i = 0; $i < $count; $i++) {
            $name = $this->faker->sentence(6);
            //ajouter une description 33/100 ie 1 fois sur 3 environ
            if (random_int(1, 100) <= 33) {
                $description = $this->faker->text(200);
            } else {
                $description = null;
            }

            $project = new Project();
            $project->setName($name);
            $project->setDescription($description);
            
            $this->manager->persist($project);
        }

        $this->manager->flush();
    }

    public function loadSchoolYear(int $count) : void {

        //il y a 2 school year par an
        // la première le 01/01
        // la seconde le 01/07
   

        // school year commence en
        $year = 2020;

        for ($i = 0; $i < $count; $i++) {
            $name = $this->faker->realText(100);
            $dateStart = new DateTime();
            $dateEnd = new DateTime();

            if ($i % 2 == 0) {
                //nombre pair
                $dateStart->setDate($year, 1, 1);
                $dateEnd->setDate($year, 6, 30);
            } else {
                //nombre impair
                $dateStart->setDate($year, 7, 1);
                $dateEnd->setDate($year, 12, 31);


            }
            // incrémentation de l'année tous les 2 school years
            if ($i != 0 && $i % 2 == 0) {
                $year++;
            }

            $schoolYear = new SchoolYear();
            $schoolYear->setName($name);
            $schoolYear->setDateStart($dateStart);
            $schoolYear->setDateEnd($dateEnd);

            $this->manager->persist($schoolYear);

        }

        $this->manager->flush();
    }

    public function loadUserSchoolYearRelation(int $countSchoolYear): void
    {
        $schoolYearRepository = $this->manager->getRepository(SchoolYear::class);
        $userRepository = $this->manager->getRepository(User::class);
        $schoolYears = $schoolYearRepository->findAll();
        // récupération de la liste des students avec la méthode array_filter()
        // $users = $userRepository->findAll();
        // $students = array_filter($users, function($user) {
        //     return in_array('ROLE_STUDENT', $user->getRoles());
        // });
        // récupération de la liste des students avec une méthode personnalisée du repository
        $students = $userRepository->findByRole('ROLE_STUDENT');
        foreach ($students as $i => $student) {
            $remainder = $i % $countSchoolYear;
            $student->setSchoolYear($schoolYears[$remainder]);
            $this->manager->persist($student);
        }
        $this->manager->flush();
    }
}

