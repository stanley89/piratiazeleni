<?php

namespace App\RedakceModule\Presenters;

use Nette,
    App\Model;
use Nette\Application\UI\Form;

/**
 * Homepage presenter.
 */
class ClankyPresenter extends BasePresenter
{


    /** @var Models/Upload */
    private $upload;

    public function injectUpload(\Models\Upload $upload)
    {
        $this->upload = $upload;
    }

    public function startup()
    {
        parent::startup();
        if (!$this->getUser()->isAllowed("clanky", "zobrazit")) {
            $this->flashMessage("Nemáte oprávnění pro přístup do této sekce.");
            $this->redirect(":Homepage:");
        }
    }

    protected function createComponentClanek()
    {
        $form = new Form;
        $form->addHidden("id");
        $form->addText("titulek", "Titulek")
            ->addRule(Form::MAX_LENGTH, "Titulek je příliš dlouhý", 255);
        $form->addText("autor", "Autor")
            ->addRule(Form::MAX_LENGTH, "Autor je příliš dlouhý", 255);

        $form->addSelect("kategorie_id", "Kategorie", $this->kategorie->getPairs());
        $form->addTextArea("perex", "Perex");
        $form->addSelect("obrazek_id", "Obrázek", $this->upload->getPairs())->setPrompt("-- bez obrázku --");
        $form->addTextArea("text", "Text");

        $form->addTextArea("stitky_text", "Štítky");
        $form->addText("skupina", "Skupina");
        $form->addSubmit("save", "Uložit");
        $form->onSuccess[] = $this->saveClanek;
        return $form;
    }

    protected function createComponentVydani()
    {
        $form = new Form;
        $form->addHidden("id");
        $form->addDatePicker("datum_vydani", "Datum vydání")
            ->setDefaultValue(date("d.m.Y"));
        $form->addText("cas_vydani", "Čas vydání")
            ->addRule(Form::PATTERN, "Zadejte prosím platný čas vydání.", "^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$")
            ->setDefaultValue(date("H:i"));

        $form->addSubmit("save", "Uložit");
        $form->onSuccess[] = $this->publishClanek;
        return $form;
    }

    protected function createComponentSouvisejici()
    {
        $form = new Form;
        $form->addHidden("clanek_id");
        $form->addSelect("souvisejici_id", "Související článek");
        $form->addSubmit("add", "Přidat");
        $form->onSuccess[] = $this->addSouvisejici;
        return $form;
    }


    public function saveClanek(Form $form)
    {
        $vals = $form->getValues();
        $clanek = $this->clanky->get($vals['id']);

        if (!$this->getUser()->isAllowed("clanky") &&
            !($this->getUser()->isAllowed("clanky", "upravit_nevydany") && (empty($clanek) || empty($clanek->datum_vydani)))
        )
        {
            $this->flashMessage("Nemáte oprávnění upravovat článek.");
            $this->redirect(":Redakce:Clanky:");
        }
        $id = $this->clanky->add($vals);
        $clanek = $this->clanky->get($id);
        $this->flashMessage("Článek " . $clanek['titulek'] . " byl uložen.");

        $this->redirect("upravit", $id);
    }

    public function publishClanek(Form $form)
    {
        $vals = $form->getValues();
        $clanek = $this->clanky->get($vals['id']);
        if (!$this->getUser()->isAllowed("clanky", "vydat")) {
            $this->flashMessage("Nemáte oprávnění vydávat články.");
            $this->redirect(":Redakce:Clanky:");
        }
        $this->clanky->publish($vals);
        $this->flashMessage("Vydání článku " . $clanek['titulek'] . " bylo naplánováno.");
        $this->redirect("default");
    }

    public function addSouvisejici($form)
    {
        if (!$this->getUser()->isAllowed("clanky", "souvisejici")) {
            $this->flashMessage("Nemáte oprávnění upravovat související články.");
            $this->redirect("this");
        }

        $vals = $form->getValues();
        $this->clanky->addSouvisejici($vals);
        $this->redirect("this");
    }

    public function handleRemoveSouvisejici($clanek_id, $souvisejici_id, $confirmed = false)
    {
        if (!$this->getUser()->isAllowed("clanky", "souvisejici")) {
            $this->flashMessage("Nemáte oprávnění upravovat související články.");
            $this->redirect("this");
        }
        if ($confirmed) {
            $this->clanky->removeSouvisejici($clanek_id, $souvisejici_id);
        }
        $this->redirect("souvisejici", $clanek_id);
    }

    public function actionUpravit($id = null)
    {
        $clanek = $this->clanky->get($id);
        if (!$this->getUser()->isAllowed("clanky") &&
            !($this->getUser()->isAllowed("clanky", "upravit_nevydany") && (empty($clanek) || empty($clanek->datum_vydani)))
        ) {
            $this->flashMessage("Nemáte oprávnění upravovat tento článek.");
            $this->redirect(":Redakce:Clanky:");
        }
        if (!empty($id)) {
            $clanek = $this->clanky->get($id);
            $count = count($clanek->stitky);
            $stitky = "";
            foreach ($clanek->stitky as $stitek) {
                if ($count > 1) {
                    $stitky .= $stitek . "\n";
                } else {
                    $stitky .= $stitek;
                }
                $count--;
            }
            $clanek['stitky_text'] = $stitky;
            $this['clanek']->setDefaults($clanek);
            $this->template->clanek = $clanek;

        }
    }

    public function actionVydat($id)
    {
        if (empty($id)) $this->redirect("default");
        $clanek = $this->clanky->get($id);
        if (!$this->getUser()->isAllowed("clanky", "vydat")) {
            $this->flashMessage("Nemáte oprávnění vydávat články.");
            $this->redirect(":Redakce:Clanky:");
        }
        if (!empty($clanek->datum_vydani)) {
            $datum = $clanek->datum_vydani->format("d.m.Y");
            $cas = $clanek->datum_vydani->format("H:i");
            $this['vydani']['datum_vydani']->setDefaultValue($datum);
            $this['vydani']['cas_vydani']->setDefaultValue($cas);
        }
        $this['vydani']['id']->setValue($id);


        $this->template->clanek = $clanek;
    }

    public function actionSouvisejici($id)
    {
        $this->template->clanek = $this->clanky->get($id);
        $this->template->clanky = $this->clanky->getSouvisejici($id, true);
        $this['souvisejici']['clanek_id']->setValue($id);
        $this['souvisejici']['souvisejici_id']->setItems($this->clanky->getPairs($id));
    }

    public function handleSmazat($id, $confirmed = false)
    {
        if (!$this->getUser()->isAllowed("clanky", "smazat")) {
            $this->flashMessage("Nemáte oprávnění mazat články.");
            $this->redirect(":Redakce:Clanky:");
        }

        $clanek = $this->clanky->get($id);
        if ($confirmed) {
            $this->clanky->delete($id);
        }
        $this->flashMessage("Článek " . $clanek->titulek . " byl smazán.");
        $this->redirect("default");
    }

    public function actionDefault()
    {
        $this->template->clanky = $this->clanky->getAll();
    }

}
