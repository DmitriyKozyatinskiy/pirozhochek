<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers as BaseRegistersUsers;
use Bestmomo\LaravelEmailConfirmation\Notifications\ConfirmEmail;
use Illuminate\Console\DetectsApplicationNamespace;
use Spatie\Newsletter\NewsletterFacade as Newsletter;
use Laravel\Cashier\Billable;
use App\User;


trait RegistersUsers
{
  use BaseRegistersUsers, DetectsApplicationNamespace, Billable;

  /**
   * Handle a registration request for the application.
   *
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function register(Request $request)
  {
    $this->validator($request->all())->validate();
    $user = User::where('email', $request->email)->first();
    if ($user) {
      return back()->with('registration-error', 'Account with this email already exists.');
    }

    $user = $this->create($request->all());
    $user->confirmation_code = str_random(30);
    $user->save();

    event(new Registered($user));

    $this->notifyUser($user);

    return back()->with('confirmation-success', trans('confirmation::confirmation.message'));
  }

  /**
   * Handle a confirmation request
   *
   * @param  integer $id
   * @param  string $confirmation_code
   * @return \Illuminate\Http\Response
   */
  public function confirm($id, $confirmation_code)
  {
    $model = config('auth.providers.users.model');

    $user = $model::whereId($id)->whereConfirmationCode($confirmation_code)->firstOrFail();
    $user->confirmation_code = null;
    $user->confirmed = true;

    if ($user->is_subscription_required) {
      Newsletter::subscribe($user->email);
      $user->is_subscribed = true;
    }

    // $user->newSubscription('main', 'monthly')->create();
    $braintreeCustomer = \Braintree_Customer::create([
      'email' => $user->email
    ]);
    $user->braintree_id = $braintreeCustomer->customer->id;

    $braintreeFreePlan = collect(\Braintree_Plan::all())->filter(function ($plan, $key) {
      return $plan->price == 0;
    })->first();

//    if ($braintreeFreePlan) {
//      $clientToken = \Braintree_ClientToken::generate();
//      $user->newSubscription('main', $braintreeFreePlan->name)->create($clientToken);
//    }

//    $freePlan = Plan::where('price', 0)->first();
//    if ($freePlan) {
//      $user->plan()->associate($freePlan);
//    }

    $user->save();

    return redirect(route('login'))->with('confirmation-success', trans('confirmation::confirmation.success'));
  }

  /**
   * Handle a resend request
   *
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function resend(Request $request)
  {
    if ($request->session()->has('user_id')) {

      $model = config('auth.providers.users.model');

      $user = $model::findOrFail($request->session()->get('user_id'));

      $this->notifyUser($user);

      return redirect(route('login'))->with('confirmation-success', trans('confirmation::confirmation.resend'));
    }

    return redirect('/');
  }

  /**
   * Notify user with email
   *
   * @param  Model $user
   * @return void
   */
  protected function notifyUser($user)
  {
    $class = $this->getAppNamespace() . 'Notifications\ConfirmEmail';

    if (!class_exists($class)) {
      $class = ConfirmEmail::class;
    }

    $user->notify(new $class);
  }
}
