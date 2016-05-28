<?php

namespace App\Presenters;

use Nette,
	App\Model;


/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{
    	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = new Nette\Application\UI\Form;
		$form->addText('username', 'Username:')
			->setRequired('Please enter your username.');

		$form->addPassword('password', 'Password:')
			->setRequired('Please enter your password.');

		$form->addCheckbox('remember', 'Keep me signed in');

		$form->addSubmit('send', 'Sign in');

		// call method signInFormSucceeded() on success
		$form->onSuccess[] = $this->signInFormSucceeded;
		return $form;
	}


	public function signInFormSucceeded($form)
	{
		$values = $form->getValues();

		if ($values->remember) {
			$this->getUser()->setExpiration('14 days', FALSE);
		} else {
			$this->getUser()->setExpiration('20 minutes', TRUE);
		}

		try {
			$this->getUser()->login($values->username, $values->password);
			$this->redirect('Homepage:');

		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}

    private $uzivatele;

    public function injectUzivatele(\Models\Uzivatele $uzivatele) {
        $this->uzivatele = $uzivatele;
    }

    private $httpRequest;

    public function injectRequest(\Nette\Http\Request $httpRequest) {
        $this->httpRequest = $httpRequest;
    }


    public function actionPirateId() {
        $openId = new \LightOpenID($this->httpRequest->getUrl()->getAuthority());
        if(!$openId->mode) {
            $openId->identity = "https://openid.pirati.cz";
            $openId->required = array(
                'namePerson',
                'namePerson/first',
                'namePerson/last',
                'contact/email',
            );
            $this->redirectUrl($openId->authUrl());
        } elseif($openId->mode == 'cancel') {
            $this->flashMessage('Uživatel zrušil přihlašování.');
        } else {
            if ($openId->validate()) {
                $uzivatel = $this->uzivatele->add($openId);
                $role = $this->uzivatele->getRole($uzivatel->id);
                $identity = new \Nette\Security\Identity($openId->identity, $role, $uzivatel);
                $this->getUser()->login($identity);
                $this->flashMessage("Uživatel přihlášen");
            } else {
                $this->flashMessage("Přihlášení se nepodařilo.");
            }
        }


        $this->redirect(":Homepage:");
    }

    public function actionMojeId() {
        $openId = new \LightOpenID($this->httpRequest->getUrl()->getAuthority());
        if(!$openId->mode) {
            $openId->identity = "https://mojeid.cz/endpoint/";
            $openId->required = array(
                'namePerson',
                'namePerson/first',
                'namePerson/last',
                'contact/email',
            );
            $this->redirectUrl($openId->authUrl());
        } elseif($openId->mode == 'cancel') {
            $this->flashMessage('Uživatel zrušil přihlašování.');
        } else {
            if ($openId->validate()) {
                $uzivatel = $this->uzivatele->add($openId);
                $role = $this->uzivatele->getRole($uzivatel->id);
                $identity = new \Nette\Security\Identity($openId->identity, $role, $uzivatel);
                $this->getUser()->login($identity);
                $this->flashMessage("Uživatel přihlášen");
            } else {
                $this->flashMessage("Přihlášení se nepodařilo.");
            }
        }


        $this->redirect(":Homepage:");
    }


    public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('Byl jste odhlášen.');
		$this->redirect('Homepage:');
	}

}
