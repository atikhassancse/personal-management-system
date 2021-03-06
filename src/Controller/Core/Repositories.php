<?php
/**
 * Created by PhpStorm.
 * User: volmarg
 * Date: 29.05.19
 * Time: 21:02
 */

namespace App\Controller\Core;


use App\Controller\Core\AjaxResponse;
use App\Entity\Modules\Contacts\MyContact;
use App\Repository\FilesSearchRepository;
use App\Repository\FilesTagsRepository;
use App\Repository\Modules\Achievements\AchievementRepository;
use App\Repository\Modules\Contacts\MyContactGroupRepository;
use App\Repository\Modules\Contacts\MyContactRepository;
use App\Repository\Modules\Contacts\MyContactTypeRepository;
use App\Repository\Modules\Goals\MyGoalsPaymentsRepository;
use App\Repository\Modules\Goals\MyGoalsRepository;
use App\Repository\Modules\Goals\MyGoalsSubgoalsRepository;
use App\Repository\Modules\Job\MyJobAfterhoursRepository;
use App\Repository\Modules\Job\MyJobHolidaysPoolRepository;
use App\Repository\Modules\Job\MyJobHolidaysRepository;
use App\Repository\Modules\Job\MyJobSettingsRepository;
use App\Repository\Modules\Notes\MyNotesRepository;
use App\Repository\Modules\Notes\MyNotesCategoriesRepository;
use App\Repository\Modules\Passwords\MyPasswordsGroupsRepository;
use App\Repository\Modules\Passwords\MyPasswordsRepository;
use App\Repository\Modules\Payments\MyPaymentsBillsItemsRepository;
use App\Repository\Modules\Payments\MyPaymentsBillsRepository;
use App\Repository\Modules\Payments\MyPaymentsIncomeRepository;
use App\Repository\Modules\Payments\MyPaymentsMonthlyRepository;
use App\Repository\Modules\Payments\MyPaymentsOwedRepository;
use App\Repository\Modules\Payments\MyPaymentsProductRepository;
use App\Repository\Modules\Payments\MyPaymentsSettingsRepository;
use App\Repository\Modules\Payments\MyRecurringPaymentMonthlyRepository;
use App\Repository\Modules\Reports\ReportsRepository;
use App\Repository\Modules\Schedules\MyScheduleRepository;
use App\Repository\Modules\Schedules\MyScheduleTypeRepository;
use App\Repository\Modules\Shopping\MyShoppingPlansRepository;
use App\Repository\Modules\Travels\MyTravelsIdeasRepository;
use App\Repository\SettingRepository;
use App\Repository\System\LockedResourceRepository;
use App\Repository\UserRepository;
use App\Services\Exceptions\ExceptionDuplicatedTranslationKey;
use App\Services\Exceptions\ExceptionRepository;
use App\Services\Core\Translator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\PersistentCollection;
use Exception;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Repositories extends AbstractController {

    const ACHIEVEMENT_REPOSITORY_NAME                   = 'AchievementRepository';
    const MY_NOTES_REPOSITORY_NAME                      = 'MyNotesRepository';
    const MY_NOTES_CATEGORIES_REPOSITORY_NAME           = 'MyNotesCategoriesRepository';
    const MY_JOB_AFTERHOURS_REPOSITORY_NAME             = 'MyJobAfterhoursRepository';
    const MY_PAYMENTS_MONTHLY_REPOSITORY_NAME           = 'MyPaymentsMonthlyRepository';
    const MY_PAYMENTS_PRODUCTS_REPOSITORY_NAME          = 'MyPaymentsProductRepository';
    const MY_PAYMENTS_SETTINGS_REPOSITORY_NAME          = 'MyPaymentsSettingsRepository';
    const MY_SHOPPING_PLANS_REPOSITORY_NAME             = 'MyShoppingPlansRepository';
    const MY_TRAVELS_IDEAS_REPOSITORY_NAME              = 'MyTravelsIdeasRepository';
    const INTEGRATIONS_RESOURCES_REPOSITORY_NAME        = 'IntegrationResourceRepository';
    const MY_PASSWORDS_REPOSITORY_NAME                  = 'MyPasswordsRepository';
    const MY_PASSWORDS_GROUPS_REPOSITORY_NAME           = 'MyPasswordsGroupsRepository';
    const USER_REPOSITORY                               = 'UserRepository';
    const MY_GOALS_REPOSITORY_NAME                      = 'MyGoalsRepository';
    const MY_SUBGOALS_REPOSITORY_NAME                   = 'MyGoalsSubgoalsRepository';
    const MY_GOALS_PAYMENTS_REPOSITORY_NAME             = 'MyGoalsPaymentsRepository';
    const MY_JOB_HOLIDAYS_REPOSITORY_NAME               = 'MyJobHolidaysRepository';
    const MY_JOB_HOLIDAYS_POOL_REPOSITORY_NAME          = 'MyJobHolidaysPoolRepository';
    const MY_JOB_SETTINGS_REPOSITORY_NAME               = 'MyJobSettingsRepository';
    const MY_PAYMENTS_OWED_REPOSITORY_NAME              = 'MyPaymentsOwedRepository';
    const MY_PAYMENTS_INCOME_REPOSITORY_NAME            = 'MyPaymentsIncomeRepository';
    const MY_PAYMENTS_BILLS_REPOSITORY_NAME             = 'MyPaymentsBillsRepository';
    const MY_PAYMENTS_BILLS_ITEMS_REPOSITORY_NAME       = 'MyPaymentsBillsItemsRepository';
    const FILE_TAGS_REPOSITORY                          = 'FilesTagsRepository';
    const REPORTS_REPOSITORY                            = 'ReportsRepository';
    const MY_RECURRING_PAYMENT_MONTHLY_REPOSITORY_NAME  = 'MyRecurringPaymentMonthlyRepository';
    const SETTING_REPOSITORY                            = 'SettingRepository';
    const MY_SCHEDULE_REPOSITORY                        = "MyScheduleRepository";
    const MY_SCHEDULE_TYPE_REPOSITORY                   = "MyScheduleTypeRepository";
    const MY_CONTACT_REPOSITORY                         = "MyContactRepository";
    const MY_CONTACT_TYPE_REPOSITORY                    = "MyContactTypeRepository";
    const MY_CONTACT_GROUP_REPOSITORY                   = "MyContactGroupRepository";
    const LOCKED_RESOURCE_REPOSITORY                    = "LockedResourceRepository";

    const PASSWORD_FIELD        = 'password';

    const KEY_MESSAGE           = "message";

    const KEY_CLASS_META_RELATED_ENTITY_FIELD_NAME          = "fieldName";
    const KEY_CLASS_META_RELATED_ENTITY_FIELD_TARGET_ENTITY = "targetEntity";
    const KEY_CLASS_META_RELATED_ENTITY_MAPPED_BY           = "mappedBy";
    const KEY_CLASS_META_RELATED_ENTITY_TARGET_ENTITY       = "targetEntity";

    const ENTITY_PROPERTY_DELETED = "deleted";
    const ENTITY_PROXY_KEY        = "Proxies";

    const ENTITY_GET_DELETED_METHOD_NAME = "getDeleted";
    const ENTITY_IS_DELETED_METHOD_NAME  = "isDeleted";

    /**
     * @var EntityManagerInterface $entity_manager
     */
    private $entity_manager;

    /**
     * @var \App\Services\Core\Translator $translator
     */
    private $translator;

    /**
     * @var MyNotesRepository $myNotesRepository
     */
    public $myNotesRepository;

    /**
     * @var AchievementRepository
     */
    public $achievementRepository;

    /**
     * @var MyJobAfterhoursRepository
     */
    public $myJobAfterhoursRepository;

    /**
     * @var MyPaymentsMonthlyRepository
     */
    public $myPaymentsMonthlyRepository;

    /**
     * @var MyPaymentsProductRepository
     */
    public $myPaymentsProductRepository;

    /**
     * @var MyShoppingPlansRepository
     */
    public $myShoppingPlansRepository;

    /**
     * @var MyTravelsIdeasRepository
     */
    public $myTravelsIdeasRepository;

    /**
     * @var MyPaymentsSettingsRepository
     */
    public $myPaymentsSettingsRepository;

    /**
     * @var MyNotesCategoriesRepository
     */
    public $myNotesCategoriesRepository;

    /**
     * @var MyPasswordsRepository
     */
    public $myPasswordsRepository;

    /**
     * @var MyPasswordsGroupsRepository
     */
    public $myPasswordsGroupsRepository;

    /**
     * @var UserRepository
     */
    public $userRepository;

    /**
     * @var MyGoalsRepository
     */
    public $myGoalsRepository;

    /**
     * @var MyGoalsSubgoalsRepository
     */
    public $myGoalsSubgoalsRepository;

    /**
     * @var MyGoalsPaymentsRepository
     */
    public $myGoalsPaymentsRepository;

    /**
     * @var MyJobHolidaysRepository
     */
    public $myJobHolidaysRepository;

    /**
     * @var MyJobHolidaysPoolRepository
     */
    public $myJobHolidaysPoolRepository;

    /**
     * @var MyJobSettingsRepository
     */
    public $myJobSettingsRepository;

    /**
     * @var MyPaymentsOwedRepository
     */
    public $myPaymentsOwedRepository;

    /**
     * @var FilesTagsRepository
     */
    public $filesTagsRepository;

    /**
     * @var FilesSearchRepository $filesSearchRepository
     */
    public $filesSearchRepository;

    /**
     * @var MyPaymentsBillsRepository $myPaymentsBillsRepository
     */
    public $myPaymentsBillsRepository;

    /**
     * @var MyPaymentsBillsItemsRepository $myPaymentsBillsItemsRepository
     */
    public $myPaymentsBillsItemsRepository;

    /**
     * @var ReportsRepository $reportsRepository
     */
    public $reportsRepository;

    /**
     * @var MyRecurringPaymentMonthlyRepository
     */
    public $myRecurringPaymentMonthlyRepository;

    /**
     * @var SettingRepository
     */
    public $settingRepository;

    /**
     * @var MyScheduleRepository $myScheduleRepository
     */
    public $myScheduleRepository;

    /**
     * @var MyScheduleTypeRepository $myScheduleTypeRepository
     */
    public $myScheduleTypeRepository;

    /**
     * @var MyContactRepository $myContactRepository
     */
    public $myContactRepository;

    /**
     * @var MyContactTypeRepository $myContactTypeRepository
     */
    public $myContactTypeRepository;

    /**
     * @var MyContactGroupRepository $myContactGroupRepository
     */
    public $myContactGroupRepository;

    /**
     * @var MyPaymentsIncomeRepository $myPaymentsIncomeRepository
     */
    public $myPaymentsIncomeRepository;

    /**
     * @var LockedResourceRepository $lockedResourceRepository
     */
    public $lockedResourceRepository;

    public function __construct(
        MyNotesRepository                   $myNotesRepository,
        AchievementRepository               $myAchievementsRepository,
        MyJobAfterhoursRepository           $myJobAfterhoursRepository,
        MyPaymentsMonthlyRepository         $myPaymentsMonthlyRepository,
        MyPaymentsProductRepository         $myPaymentsProductRepository,
        MyShoppingPlansRepository           $myShoppingPlansRepository,
        MyTravelsIdeasRepository            $myTravelIdeasRepository,
        MyPaymentsSettingsRepository        $myPaymentsSettingsRepository,
        MyNotesCategoriesRepository         $myNotesCategoriesRepository,
        MyPasswordsRepository               $myPasswordsRepository,
        MyPasswordsGroupsRepository         $myPasswordsGroupsRepository,
        UserRepository                      $userRepository,
        MyGoalsRepository                   $myGoalsRepository,
        MyGoalsSubgoalsRepository           $myGoalsSubgoalsRepository,
        MyGoalsPaymentsRepository           $myGoalsPaymentsRepository,
        MyJobHolidaysRepository             $myJobHolidaysRepository,
        MyJobHolidaysPoolRepository         $myJobHolidaysPoolRepository,
        MyJobSettingsRepository             $myJobSettingsRepository,
        MyPaymentsOwedRepository            $myPaymentsOwedRepository,
        FilesTagsRepository                 $filesTagsRepository,
        FilesSearchRepository               $filesSearchRepository,
        Translator                          $translator,
        MyPaymentsBillsRepository           $myPaymentsBillsRepository,
        MyPaymentsBillsItemsRepository      $myPaymentsBillsItemsRepository,
        ReportsRepository                   $reportsRepository,
        MyRecurringPaymentMonthlyRepository $myRecurringMonthlyPaymentRepository,
        SettingRepository                   $settingRepository,
        MyScheduleRepository                $myScheduleRepository,
        MyScheduleTypeRepository            $myScheduleTypeRepository,
        MyContactTypeRepository             $myContactTypeRepository,
        MyContactGroupRepository            $myContactGroupRepository,
        MyContactRepository                 $myContactRepository,
        MyPaymentsIncomeRepository          $myPaymentsIncomeRepository,
        LockedResourceRepository            $lockedResourceRepository,
        EntityManagerInterface              $entity_manager
    ) {
        $this->myNotesRepository                    = $myNotesRepository;
        $this->achievementRepository                = $myAchievementsRepository;
        $this->myJobAfterhoursRepository            = $myJobAfterhoursRepository;
        $this->myPaymentsMonthlyRepository          = $myPaymentsMonthlyRepository;
        $this->myPaymentsProductRepository          = $myPaymentsProductRepository;
        $this->myShoppingPlansRepository            = $myShoppingPlansRepository;
        $this->myTravelsIdeasRepository             = $myTravelIdeasRepository;
        $this->myPaymentsSettingsRepository         = $myPaymentsSettingsRepository;
        $this->myNotesCategoriesRepository          = $myNotesCategoriesRepository;
        $this->myPasswordsRepository                = $myPasswordsRepository;
        $this->myPasswordsGroupsRepository          = $myPasswordsGroupsRepository;
        $this->userRepository                       = $userRepository;
        $this->myGoalsRepository                    = $myGoalsRepository;
        $this->myGoalsSubgoalsRepository            = $myGoalsSubgoalsRepository;
        $this->myGoalsPaymentsRepository            = $myGoalsPaymentsRepository;
        $this->myJobHolidaysRepository              = $myJobHolidaysRepository;
        $this->myJobHolidaysPoolRepository          = $myJobHolidaysPoolRepository;
        $this->myJobSettingsRepository              = $myJobSettingsRepository;
        $this->myPaymentsOwedRepository             = $myPaymentsOwedRepository;
        $this->filesTagsRepository                  = $filesTagsRepository;
        $this->filesSearchRepository                = $filesSearchRepository;
        $this->translator                           = $translator;
        $this->myPaymentsBillsRepository            = $myPaymentsBillsRepository;
        $this->myPaymentsBillsItemsRepository       = $myPaymentsBillsItemsRepository;
        $this->reportsRepository                    = $reportsRepository;
        $this->myRecurringPaymentMonthlyRepository  = $myRecurringMonthlyPaymentRepository;
        $this->settingRepository                    = $settingRepository;
        $this->myScheduleRepository                 = $myScheduleRepository;
        $this->myScheduleTypeRepository             = $myScheduleTypeRepository;
        $this->myContactTypeRepository              = $myContactTypeRepository;
        $this->myContactGroupRepository             = $myContactGroupRepository;
        $this->myContactRepository                  = $myContactRepository;
        $this->myPaymentsIncomeRepository           = $myPaymentsIncomeRepository;
        $this->lockedResourceRepository             = $lockedResourceRepository;
        $this->entity_manager                       = $entity_manager;
    }

    /**
     * @param string $repository_name
     * @param $id
     * This is general method for all common record soft delete called from front
     * @param array $findByParams
     * @param Request|null $request
     * @return Response
     * @throws ExceptionDuplicatedTranslationKey
     */
    public function deleteById(string $repository_name, $id, array $findByParams = [], ?Request $request = null ): Response {
        try {

            $id         = $this->trimAndCheckId($id);
            $repository = $this->{lcfirst($repository_name)};
            $record     = $repository->find($id);

            if ( $this->hasRecordActiveRelatedEntities($record, $repository) ) {
                $message = $this->translator->translate('exceptions.repositories.recordHasChildrenCannotRemove');

                if( !empty($request) ){
                    return AjaxResponse::buildResponseForAjaxCall(500, $message);
                }

                return new Response($message, 500);
            }

            #Info: Reattach the entity - doctrine based issue
            $this->entity_manager->clear();
            $record = $repository->find($id);

            $record->setDeleted(1);
            $record = $this->changeRecordData($repository_name, $record);

            $em = $this->getDoctrine()->getManager();

            $em->persist($record);
            $em->flush();

            $message = $this->translator->translate('responses.repositories.recordDeletedSuccessfully');

            if( !empty($request) ){
                return AjaxResponse::buildResponseForAjaxCall(200, $message);
            }

            return new Response($message, 200);
        } catch (Exception | ExceptionRepository $er) {
            $message = $this->translator->translate('responses.repositories.couldNotDeleteRecord');

            if( !empty($request) ){
                return AjaxResponse::buildResponseForAjaxCall(500, $message);
            }

            return new Response($message, 500);
        }
    }

    /**
     * @param array $parameters
     * @param $entity
     * This is general method for all common record update called from front
     * @param array $findByParams
     * @return JsonResponse
     *
     * It's required that field for which You want to get entity has this example format in js ajax request:
     * 'category': {
     * "type": "entity",
     * 'namespace': 'App\\Entity\\MyNotesCategories',
     * 'id': $(noteCategoryId).val(),
     * },
     * @throws ExceptionDuplicatedTranslationKey
     */
    public function update(array $parameters, $entity, array $findByParams = []) {

        try {
            unset($parameters['id']);

            foreach ($parameters as $parameter => $value) {

                /**
                 * The only situation where this will be array is entity type parameter - does not need to be trimmed
                 */
                if(!is_array($value)){
                    $value = trim($value);
                }

                if ($value === "true") {
                    $value = true;
                }
                if ($value === "false") {
                    $value = false;
                }

                $isParameterValid = $this->isParameterValid($parameter, $value);

                if (!$isParameterValid) {
                    $message = $this->translator->translate('responses.general.invalidParameterValue');
                    return new JsonResponse($message, 500);
                }

                if (is_array($value)) {
                    if (array_key_exists('type', $value) && $value['type'] == 'entity') {
                        $value = $this->getEntity($value);
                    }
                }

                $methodName     = 'set' . ucfirst($parameter);
                $hasRelation    = strstr($methodName, '_id');
                $methodName     = ( $hasRelation ? str_replace('_id', 'Id', $methodName) : $methodName);

                $value          = ( $hasRelation && empty($value) ? null : $value ); // relation field is allowed to be empty sometimes

                if (is_object($value)) {
                    $entity->$methodName($value);
                    continue;
                }
                $entity->$methodName($value);

            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $message = $this->translator->translate('responses.repositories.recordUpdateSuccess');
            return new JsonResponse($message, 200);
        } catch (ExceptionRepository $er) {
            $message = $this->translator->translate('responses.repositories.recordUpdateFail');
            return new JsonResponse($message, 500);
        }
    }

    /**
     * @param array $entity_data
     * @return object|null
     */
    private function getEntity(array $entity_data) {
        $entity = null;

        try {

            if (array_key_exists('namespace', $entity_data) && array_key_exists('id', $entity_data)) {
                $entity = $this->getDoctrine()->getRepository($entity_data['namespace'])->find($entity_data['id']);
            }

        } catch (ExceptionRepository $er) {
            echo $er->getMessage();
        }

        return $entity;
    }

    /**
     * @param $record
     * @param EntityRepository $repository
     * @return bool
     * This method is used to define weather the record can be soft deleted or not,
     * it has to handle all the variety of associations between records
     * because for example it should not be possible to remove category when there are some other records in it
     */
    private function hasRecordActiveRelatedEntities($record, $repository): bool {

        # First if this is not entity for some reason then ignore it, as this applies only to entities
        $record_class_name  = get_class($record);
        $class_meta         = $this->entity_manager->getClassMetadata($record_class_name);
        $table_name         = $class_meta->getTableName();
        $is_record_entity  = $this->isEntityClass($record_class_name);

        if( !$is_record_entity ){
            return false;
        }

        # Second thing is that some tables have relation to self without foreign key - this must be checked separately
        $parent_keys       = ['parent', 'parent_id', 'parentId', 'parentID'];
        $has_self_relation = false;

        foreach ($parent_keys as $key) {

            if (property_exists($record, $key)) {
                $child_record      = $repository->findBy([$key => $record->getId(), self::ENTITY_PROPERTY_DELETED => 0]);
                $has_self_relation = true;
            }

            if (
                    isset($child_record)
                &&  !empty($child_record)
                &&  (
                        (
                                method_exists($child_record, self::ENTITY_GET_DELETED_METHOD_NAME)
                            &&  !$child_record->getDeleted()
                        )
                        ||
                        (
                                method_exists($child_record, self::ENTITY_IS_DELETED_METHOD_NAME)
                            &&  !$child_record->isDeleted()
                        )
                    )
            ) {
                return true;
            }

        }

        # Third
        # We need to check weather we deal with parent or child
        # the child can be removed, the problem is parent as with active children he must stay
        # symfony adds _id to every foreign key so if there is property with _id we can assume that it is a children
        # info: this may cause problems if there will be advanced(i doubt there will) relations (not just parent/child)

        $columns_names = $this->getColumnsNamesForTableName($table_name);

        foreach( $columns_names as $column_name ){
            # we have a child so we can remove it
            if( strstr($column_name, "_id") && !$has_self_relation ){
                return false;
            }
        }

        # we have parent so we need to check what's the state of all of his children
        # we have to find which fields are relational ones

        $associations_mappings = $class_meta->getAssociationMappings();

        foreach( $associations_mappings as $association_mapping ){
            $field_name = $association_mapping[self::KEY_CLASS_META_RELATED_ENTITY_FIELD_NAME];

            # info: might cause problems in case of using non standard methods name (instead of the ones generated by entity)
            # build getter method name and if such exists then check children

            $method_name = "get" .  ucfirst($field_name);

            if( !method_exists($record, $method_name) ){
                continue;
            }

            $related_entities = $record->$method_name()->getValues();
            foreach( $related_entities as $related_entity ){

                if( method_exists($related_entity, self::ENTITY_GET_DELETED_METHOD_NAME) ){
                    $is_deleted = $related_entity->getDeleted();

                    if(!$is_deleted){
                        return true;
                    }
                }

                if( method_exists($related_entity, self::ENTITY_IS_DELETED_METHOD_NAME) ){
                    $is_deleted = $related_entity->isDeleted();

                    if(!$is_deleted){
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param array $columns_names
     */
    public static function removeHelperColumnsFromView(array &$columns_names) {
        $columns_to_remove = ['deleted', 'delete'];

        foreach ($columns_to_remove as $column_to_remove) {
            $key = array_search($column_to_remove, $columns_names);

            if (!is_null($key) && $key) {
                unset($columns_names[$key]);
            }

        }
    }

    /**
     * This function trims id and rechecks if it's int
     * The problem is that js keeps getting all the whitespaces and new lines in to many places....
     *
     * @param $id
     * @return string
     * @throws Exception
     */
    public function trimAndCheckId($id){
        $id = (int) trim($id);

        if (!is_numeric($id)) {
            $message = $this->translator->translate('responses.repositories.inorrectId') . $id;
            throw new Exception($message);
        }

        return $id;
    }

    /**
     * This function validates given fields by set of rules
     * @param string $parameter
     * @param $value
     * @return bool
     */
    private function isParameterValid(string $parameter, $value):bool
    {
        switch( $parameter ){
            case static::PASSWORD_FIELD:
                $isValid = !empty($value);
                break;
            default:
                $isValid = true;
        }

        return $isValid;
    }

    /**
     * This function changes the record before soft delete
     * @param string $repository_name
     * @param $record
     * @return MyContact
     */
    private function changeRecordData(string $repository_name, $record) {

        switch( $repository_name ){
            case self::MY_CONTACT_REPOSITORY:
                /**
                 * @var MyContact $record
                 */
                $record->setGroup(NULL);
                break;
        }

        return $record;
    }

    /**
     * @param $class
     * @return bool
     */
    private function isEntityClass(string $class): bool
    {
        $is_entity_class = !$this->entity_manager->getMetadataFactory()->isTransient($class);
        return $is_entity_class;
    }

    /**
     * @param string $table_name
     * @return array
     */
    private function getColumnsNamesForTableName(string $table_name): array
    {
        $schema_manager = $this->entity_manager->getConnection()->getSchemaManager();
        $columns        = $schema_manager->listTableColumns($table_name);

        $columns_names = [];
        foreach($columns as $column){
            $columns_names[] = $column->getName();
        }

        return $columns_names;
    }
}
