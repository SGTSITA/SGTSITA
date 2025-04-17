@extends('layouts.app')

@section('template_title')
   Editar Cotizacion
@endsection

@section('content')

    <div class="contaboleta_liberacionr-fluid">
        <div class="row">
            
        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-body p-0 product-checkout">
                                    <ul class="nav nav-tabs tab-style-2 d-sm-flex d-block border-bottom border-block-end-dashed" id="myTab1" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="order-tab" data-bs-toggle="tab" data-bs-target="#order-tab-pane" type="button" role="tab" aria-controls="order-tab" aria-selected="true"><i class="ri-truck-line me-2 align-middle"></i>Viaje</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="confirmed-tab" data-bs-toggle="tab" data-bs-target="#confirm-tab-pane" type="button" role="tab" aria-controls="confirmed-tab" aria-selected="false" tabindex="-1"><i class="ri-user-3-line me-2 align-middle"></i>Personal Details</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="shipped-tab" data-bs-toggle="tab" data-bs-target="#shipped-tab-pane" type="button" role="tab" aria-controls="shipped-tab" aria-selected="false" tabindex="-1"><i class="ri-bank-card-line me-2 align-middle"></i>Documentación</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="delivered-tab" data-bs-toggle="tab" data-bs-target="#delivery-tab-pane" type="button" role="tab" aria-controls="delivered-tab" aria-selected="false" tabindex="-1"><i class="ri-checkbox-circle-line me-2 align-middle"></i>Gastos</button>
                                        </li>
                                    </ul>
                                    <div class="tab-content" id="myTabContent">
                                        <div class="tab-pane fade border-0 p-0 active show" id="order-tab-pane" role="tabpanel" aria-labelledby="order-tab-pane" tabindex="0">
                                            <div class="p-4">
                                                <p class="mb-1 fw-semibold text-muted op-5 fs-20">01</p>
                                                <div class="fs-15 fw-semibold d-sm-flex d-block align-items-center justify-content-between mb-3">
                                                    <h1 class="page-title fw-semibold fs-18 mb-0">Información Cotización</h1>
                                                    
                                                    <div class="mt-sm-0 mt-2">
                                                    <div>
                                                    <div> <span class="badge bg-primary-transparent"> Estimated delivery : 30,Nov 2022 </span> </div>
                                            </div>
                                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-new-address"><i class="ri-add-line me-1 align-middle fs-14 fw-semibold d-inline-block"></i>Add New Address</button>
                                                        <div class="modal fade" id="modal-new-address" tabindex="-1" aria-labelledby="modal-new-address" aria-hidden="true">
                                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h6 class="modal-title" id="staticBackdropLabel">New Address
                                                                        </h6>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="row gy-3">
                                                                            <div class="col-xl-6">
                                                                                <label for="fullname-new" class="form-label">Full Name</label>
                                                                                <input type="text" class="form-control" id="fullname-new" placeholder="Full Name">
                                                                            </div>
                                                                            <div class="col-xl-6">
                                                                                <label for="email-new" class="form-label">Email</label>
                                                                                <input type="email" class="form-control" id="email-new" placeholder="email">
                                                                            </div>
                                                                            <div class="col-xl-6">
                                                                                <label for="phonenumber-new" class="form-label">Phone Number</label>
                                                                                <input type="number" class="form-control" id="phonenumber-new" placeholder="Phone">
                                                                            </div>
                                                                            <div class="col-xl-6">
                                                                                <label for="address-new" class="form-label">Address</label>
                                                                                <input type="text" class="form-control" id="address-new" placeholder="Address">
                                                                            </div>
                                                                            <div class="col-xl-12">
                                                                                <div class="row">
                                                                                    <div class="col-xl-3">
                                                                                        <label for="pincode-new" class="form-label">Pincode</label>
                                                                                        <input type="number" class="form-control" id="pincode-new" placeholder="Pinicode">
                                                                                    </div>
                                                                                    <div class="col-xl-3">
                                                                                        <label for="city-new" class="form-label">City</label>
                                                                                        <input type="text" class="form-control" id="city-new" placeholder="City">
                                                                                    </div>
                                                                                    <div class="col-xl-3">
                                                                                        <label for="state-new" class="form-label">State</label>
                                                                                        <input type="text" class="form-control" id="state-new" placeholder="State">
                                                                                    </div>
                                                                                    <div class="col-xl-3">
                                                                                        <label for="country-new" class="form-label">Country</label>
                                                                                        <input type="text" class="form-control" id="country-new" placeholder="Country">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                                        <button type="button" class="btn btn-success">Save
                                                                            Address</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-xl-6">
                                                        <div class="card custom-card border shadow-none">
                                                            <div class="card-header">
                                                            <div class="card-title">Cliente</div>
                                                                <div class="d-flex align-items-center w-100">
                                                                    <div class="me-2">
                                                                        <span class="avatar avatar-rounded">
                                                                            <img src="/assets/images/faces/11.jpg" alt="img">
                                                                        </span>
                                                                    </div>
                                                                    <div class="">
                                                                        <div class="fs-15 fw-semibold">Adam Smith</div>
                                                                        <p class="mb-0 text-muted fs-11">demo@gmail.com</p>
                                                                    </div>
                                                                    <div class="dropdown ms-auto">
                                                                        <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-light" data-bs-toggle="dropdown" aria-expanded="false">
                                                                            <i class="fe fe-search"></i>
                                                                        </a>
                                                                        <ul class="dropdown-menu" style="">
                                                                            <li><a class="dropdown-item" href="javascript:void(0);">Week</a></li>
                                                                            <li><a class="dropdown-item" href="javascript:void(0);">Month</a></li>
                                                                            <li><a class="dropdown-item" href="javascript:void(0);">Year</a></li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="card-footer">
                                                                <div class="d-flex justify-content-between">
                                                                    <div class="fs-semibold fs-14">Cliente</div>
                                                                    <div class="fw-semibold text-success">Assistant Professor</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <div class="card custom-card border shadow-none">
                                                            <div class="card-header">
                                                                <div class="card-title">Sub Cliente</div>
                                                                <div class="d-flex align-items-center w-100">
                                                                    <div class="me-2">
                                                                        <span class="avatar avatar-rounded">
                                                                            <img src="/assets/images/faces/11.jpg" alt="img">
                                                                        </span>
                                                                    </div>
                                                                    <div class="">
                                                                        <div class="fs-15 fw-semibold">Adam Smith</div>
                                                                        <p class="mb-0 text-muted fs-11">28 Years, Male</p>
                                                                    </div>
                                                                    <div class="dropdown ms-auto">
                                                                        <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-light" data-bs-toggle="dropdown" aria-expanded="false">
                                                                            <i class="fe fe-search"></i>
                                                                        </a>
                                                                        <ul class="dropdown-menu" style="">
                                                                            <li><a class="dropdown-item" href="javascript:void(0);">Week</a></li>
                                                                            <li><a class="dropdown-item" href="javascript:void(0);">Month</a></li>
                                                                            <li><a class="dropdown-item" href="javascript:void(0);">Year</a></li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="card-footer">
                                                                <div class="d-flex justify-content-between">
                                                                    <div class="fw-semibold fs-14">SubCliente</div>
                                                                    <div class="fw-semibold text-success">Assistant Professor</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card custom-card border shadow-none mb-3">
                                                            <div class="card-header">
                                                                <div class="card-title">
                                                                    Payment Methods
                                                                </div>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="btn-group mb-3 d-sm-flex d-block" role="group" aria-label="Basic radio toggle button group">
                                                                    <input type="radio" class="btn-check" name="btnradio" id="btnradio1">
                                                                    <label class="btn btn-outline-light text-default mt-sm-0 mt-1" for="btnradio1">C.O.D(Cash on delivery)</label>
                                                                    <input type="radio" class="btn-check" name="btnradio" id="btnradio2">
                                                                    <label class="btn btn-outline-light text-default mt-sm-0 mt-1" for="btnradio2">UPI</label>
                                                                    <input type="radio" class="btn-check" name="btnradio" id="btnradio3" checked="">
                                                                    <label class="btn btn-outline-light text-default mt-sm-0 mt-1" for="btnradio3">Credit/Debit Card</label>
                                                                </div>
                                                                <div class="row gy-3">
                                                                    <div class="col-xl-12">
                                                                        <label for="payment-card-number" class="form-label">Card Number</label>
                                                                        <input type="text" class="form-control" id="payment-card-number" placeholder="Card Number" value="1245 - 5447 - 8934 - XXXX" onfocus="focused(this)" onfocusout="defocused(this)">
                                                                    </div>
                                                                    <div class="col-xl-12">
                                                                        <label for="payment-card-name" class="form-label">Name On Card</label>
                                                                        <input type="text" class="form-control" id="payment-card-name" placeholder="Name On Card" value="JSON TAYLOR" onfocus="focused(this)" onfocusout="defocused(this)">
                                                                    </div>
                                                                    <div class="col-xl-4">
                                                                        <label for="payment-cardexpiry-date" class="form-label">Expiration Date</label>
                                                                        <input type="text" class="form-control" id="payment-cardexpiry-date" placeholder="MM/YY" value="08/2024" onfocus="focused(this)" onfocusout="defocused(this)">
                                                                    </div>
                                                                    <div class="col-xl-4">
                                                                        <label for="payment-cvv" class="form-label">CVV</label>
                                                                        <input type="text" class="form-control" id="payment-cvv" placeholder="XXX" value="341" onfocus="focused(this)" onfocusout="defocused(this)">
                                                                    </div>
                                                                    <div class="col-xl-4">
                                                                        <label for="payment-security" class="form-label">O.T.P</label>
                                                                        <input type="text" class="form-control" id="payment-security" placeholder="XXXXXX" value="183467" onfocus="focused(this)" onfocusout="defocused(this)">
                                                                        <label for="payment-security" class="form-label mt-1 text-success fs-11"><sup><i class="ri-star-s-fill"></i></sup>Do not share O.T.P with anyone</label>
                                                                    </div>
                                                                    <div class="col-xl-12">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input form-checked-success" type="checkbox" value="" id="payment-card-save" checked="">
                                                                            <label class="form-check-label" for="payment-card-save">
                                                                                Save this card
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="card-footer">
                                                                <div class="row gy-3">
                                                                    <p class="fs-15 fw-semibold mb-1">Saved Cards :</p>
                                                                    <div class="col-xl-6">
                                                                        <div class="form-check payment-card-container mb-0 lh-1">
                                                                            <input id="payment-card1" name="payment-cards" type="radio" class="form-check-input" checked="">
                                                                            <div class="form-check-label">
                                                                               <div class="d-sm-flex d-block align-items-center justify-content-between">
                                                                                   <div class="me-2 lh-1">
                                                                                       <span class="avatar avatar-md">
                                                                                           <img src="../assets/images/ecommerce/png/26.png" alt="">
                                                                                       </span>
                                                                                   </div>
                                                                                   <div class="saved-card-details">
                                                                                       <p class="mb-0 fw-semibold">XXXX - XXXX - XXXX - 7646</p>
                                                                                   </div>
                                                                               </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-xl-6">
                                                                        <div class="form-check payment-card-container mb-0 lh-1">
                                                                            <input id="payment-card2" name="payment-cards" type="radio" class="form-check-input">
                                                                            <div class="form-check-label">
                                                                               <div class="d-sm-flex d-block align-items-center justify-content-between">
                                                                                   <div class="me-2 lh-1">
                                                                                       <span class="avatar avatar-md">
                                                                                           <img src="../assets/images/ecommerce/png/27.png" alt="">
                                                                                       </span>
                                                                                   </div>
                                                                                   <div class="saved-card-details">
                                                                                       <p class="mb-0 fw-semibold">XXXX - XXXX - XXXX - 9556</p>
                                                                                   </div>
                                                                               </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                <div class="row gy-4 mb-4">
                                                    <div class="col-xl-6">
                                                        <div class="form-floating">
                                                            <input type="text" class="form-control" id="fullname-add" value="Json Taylor" placeholder="Name">
                                                            <label for="fullname-add">Full Name</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <div class="form-floating">
                                                            <input type="email" class="form-control" id="email-add" value="jsontaylor2413@gmail.com" placeholder="name@example.com">
                                                            <label for="email-add">Email</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <div class="form-floating">
                                                            <input type="email" class="form-control is-valid" id="phoneno-add" value="(555) 555-1234" placeholder="1234-XX-XXXX">
                                                            <label for="phoneno-add">Phone No</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <div class="form-floating">
                                                            <textarea class="form-control" placeholder="Address Here" id="address-add">MIG-1-11,Monroe Street,Washington D.C,USA</textarea>
                                                            <label for="address-add">Address</label>
                                                        </div>
                                                        <div class="form-check mt-1">
                                                            <input class="form-check-input form-checked-outline form-checked-success" type="checkbox" value="" id="invalidCheck" required="" checked="">
                                                            <label class="form-check-label text-success" for="invalidCheck">
                                                                Same as Billing Address ?
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-12">
                                                        <div class="row gy-2">
                                                            <div class="col-xl-3">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control is-valid" id="pincode-add" value="20071" placeholder="Name">
                                                                    <label for="pincode-add">Pin Code</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-xl-3">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control" id="city-add" value="Georgetown" placeholder="Name">
                                                                    <label for="city-add">City</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-xl-3">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control" id="state-add" value="Washington, D.C" placeholder="Name">
                                                                    <label for="state-add">State</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-xl-3">
                                                                <div class="form-floating">
                                                                    <input type="text" class="form-control" id="country-add" value="USA" placeholder="Name">
                                                                    <label for="country-add">Country</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row gy-3">
                                                    <p class="fs-15 fw-semibold mb-1">Shipping Methods :</p>
                                                    <div class="col-xl-6">
                                                        <div class="form-check shipping-method-container mb-0">
                                                            <input id="shipping-method1" name="shipping-methods" type="radio" class="form-check-input" checked="">
                                                            <div class="form-check-label">
                                                               <div class="d-sm-flex align-items-center justify-content-between">
                                                                   <div class="me-2">
                                                                       <span class="avatar avatar-md">
                                                                           <img src="../assets/images/ecommerce/png/28.png" alt="">
                                                                       </span>
                                                                   </div>
                                                                   <div class="shipping-partner-details me-sm-5 me-0">
                                                                       <p class="mb-0 fw-semibold">UPS</p>
                                                                       <p class="text-muted fs-11 mb-0">Delivered By 24,Nov 2022</p>
                                                                   </div>
                                                                   <div class="fw-semibold me-sm-5 me-0">
                                                                       $9.99
                                                                   </div>
                                                               </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <div class="form-check shipping-method-container mb-0">
                                                            <input id="shipping-method2" name="shipping-methods" type="radio" class="form-check-input">
                                                            <div class="form-check-label">
                                                               <div class="d-sm-flex align-items-center justify-content-between">
                                                                   <div class="me-2">
                                                                       <span class="avatar avatar-md">
                                                                           <img src="../assets/images/ecommerce/png/31.png" alt="">
                                                                       </span>
                                                                   </div>
                                                                   <div class="shipping-partner-details me-sm-5 me-0">
                                                                       <p class="mb-0 fw-semibold">USPS</p>
                                                                       <p class="text-muted fs-11 mb-0">Delivered By 22,Nov 2022</p>
                                                                   </div>
                                                                   <div class="fw-semibold me-sm-5 me-0">
                                                                       $10.49
                                                                   </div>
                                                               </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <div class="form-check shipping-method-container mb-0">
                                                            <input id="shipping-method3" name="shipping-methods" type="radio" class="form-check-input">
                                                            <div class="form-check-label">
                                                               <div class="d-sm-flex align-items-center justify-content-between">
                                                                   <div class="me-2">
                                                                       <span class="avatar avatar-md">
                                                                           <img src="../assets/images/ecommerce/png/29.png" alt="">
                                                                       </span>
                                                                   </div>
                                                                   <div class="shipping-partner-details me-sm-5 me-0">
                                                                       <p class="mb-0 fw-semibold">FedEx</p>
                                                                       <p class="text-muted fs-11 mb-0">Delivered Tomorrow</p>
                                                                   </div>
                                                                   <div class="fw-semibold me-sm-5 me-0">
                                                                       $12.29
                                                                   </div>
                                                               </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <div class="form-check shipping-method-container mb-0">
                                                            <input id="shipping-method4" name="shipping-methods" type="radio" class="form-check-input">
                                                            <div class="form-check-label">
                                                               <div class="d-sm-flex align-items-center justify-content-between">
                                                                   <div class="me-2">
                                                                       <span class="avatar avatar-md">
                                                                           <img src="../assets/images/ecommerce/png/30.png" alt="">
                                                                       </span>
                                                                   </div>
                                                                   <div class="shipping-partner-details me-sm-5 me-0">
                                                                       <p class="mb-0 fw-semibold">DHL</p>
                                                                       <p class="text-muted fs-11 mb-0">Delivered Today</p>
                                                                   </div>
                                                                   <div class="fw-semibold me-sm-5 me-0">
                                                                       $18.99
                                                                   </div>
                                                               </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="px-4 py-3 border-top border-block-start-dashed d-sm-flex justify-content-end">
                                                <button type="button" class="btn btn-success-light" id="personal-details-trigger">Personal Details<i class="ri-user-3-line ms-2 align-middle d-inline-block"></i></button>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade border-0 p-0" id="confirm-tab-pane" role="tabpanel" aria-labelledby="confirm-tab-pane" tabindex="0">
                                            <div class="p-4">
                                                <p class="mb-1 fw-semibold text-muted op-5 fs-20">02</p>
                                                <div class="fs-15 fw-semibold d-sm-flex d-block align-items-center justify-content-between mb-3">
                                                    <div>Personal Details :</div>
                                                </div>
                                                <div class="row gy-4">
                                                    <div class="col-xl-6">
                                                        <label for="firstname-personal" class="form-label">First Name</label>
                                                        <input type="text" class="form-control" id="firstname-personal" placeholder="First Name" value="Json">
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <label for="lastname-personal" class="form-label">Last Name</label>
                                                        <input type="text" class="form-control" id="lastname-personal" placeholder="Last Name" value="Taylor">
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <label for="email-personal" class="form-label">Email</label>
                                                        <input type="email" class="form-control" id="email-personal" placeholder="xyz@example.com" value="">
                                                    </div>
                                                    <div class="col-xl-6">
                                                        <label for="phoneno-personal" class="form-label">Phone no</label>
                                                        <input type="text" class="form-control" id="phoneno-personal" placeholder="(555)-555-1234" value="">
                                                    </div>
                                                    <div class="col-xxl-2">
                                                        <label for="pincode-personal" class="form-label">Pincode</label>
                                                        <input type="text" class="form-control" id="pincode-personal" placeholder="200017" value="">
                                                    </div>
                                                    <div class="col-xxl-4">
                                                        <label for="choices-single-default" class="form-label">City</label>
                                                        <div class="choices" data-type="select-one" tabindex="0" role="combobox" aria-autocomplete="list" aria-haspopup="true" aria-expanded="false"><div class="choices__inner"><select class="form-control choices__input" data-trigger="" name="choices-single-default" id="choices-single-default" hidden="" tabindex="-1" data-choice="active"><option value="Choice 1" data-custom-properties="[object Object]">Georgetown</option></select><div class="choices__list choices__list--single"><div class="choices__item choices__item--selectable" data-item="" data-id="1" data-value="Choice 1" data-custom-properties="[object Object]" aria-selected="true">Georgetown</div></div></div><div class="choices__list choices__list--dropdown" aria-expanded="false"><input type="text" class="choices__input choices__input--cloned" autocomplete="off" autocapitalize="off" spellcheck="false" role="textbox" aria-autocomplete="list" aria-label="This is a placeholder set in the config" placeholder="Search"><div class="choices__list" role="listbox"><div id="choices--choices-single-default-item-choice-1" class="choices__item choices__item--choice choices__item--selectable is-highlighted" role="option" data-choice="" data-id="1" data-value="Choice 2" data-select-text="Press to select" data-choice-selectable="" aria-selected="true">Alexandria</div><div id="choices--choices-single-default-item-choice-2" class="choices__item choices__item--choice choices__item--selectable" role="option" data-choice="" data-id="2" data-value="Choice 4" data-select-text="Press to select" data-choice-selectable="">Frederick</div><div id="choices--choices-single-default-item-choice-3" class="choices__item choices__item--choice is-selected choices__item--selectable" role="option" data-choice="" data-id="3" data-value="Choice 1" data-select-text="Press to select" data-choice-selectable="">Georgetown</div><div id="choices--choices-single-default-item-choice-4" class="choices__item choices__item--choice choices__item--selectable" role="option" data-choice="" data-id="4" data-value="Choice 3" data-select-text="Press to select" data-choice-selectable="">Rockville</div></div></div></div>
                                                    </div>
                                                    <div class="col-xxl-4">
                                                        <label for="choices-single-default1" class="form-label">State</label>
                                                        <div class="choices" data-type="select-one" tabindex="0" role="combobox" aria-autocomplete="list" aria-haspopup="true" aria-expanded="false"><div class="choices__inner"><select class="form-control choices__input" data-trigger="" id="choices-single-default1" hidden="" tabindex="-1" data-choice="active"><option value="Choice 1" data-custom-properties="[object Object]">Washington,D.C</option></select><div class="choices__list choices__list--single"><div class="choices__item choices__item--selectable" data-item="" data-id="1" data-value="Choice 1" data-custom-properties="[object Object]" aria-selected="true">Washington,D.C</div></div></div><div class="choices__list choices__list--dropdown" aria-expanded="false"><input type="text" class="choices__input choices__input--cloned" autocomplete="off" autocapitalize="off" spellcheck="false" role="textbox" aria-autocomplete="list" aria-label="This is a placeholder set in the config" placeholder="Search"><div class="choices__list" role="listbox"><div id="choices--choices-single-default1-item-choice-1" class="choices__item choices__item--choice choices__item--selectable is-highlighted" role="option" data-choice="" data-id="1" data-value="Choice 4" data-select-text="Press to select" data-choice-selectable="" aria-selected="true">Alaska</div><div id="choices--choices-single-default1-item-choice-2" class="choices__item choices__item--choice choices__item--selectable" role="option" data-choice="" data-id="2" data-value="Choice 2" data-select-text="Press to select" data-choice-selectable="">California</div><div id="choices--choices-single-default1-item-choice-3" class="choices__item choices__item--choice choices__item--selectable" role="option" data-choice="" data-id="3" data-value="Choice 3" data-select-text="Press to select" data-choice-selectable="">Texas</div><div id="choices--choices-single-default1-item-choice-4" class="choices__item choices__item--choice is-selected choices__item--selectable" role="option" data-choice="" data-id="4" data-value="Choice 1" data-select-text="Press to select" data-choice-selectable="">Washington,D.C</div></div></div></div>
                                                    </div>
                                                    <div class="col-xxl-2">
                                                        <label for="country-personal" class="form-label">Country</label>
                                                        <input type="text" class="form-control" id="country-personal" placeholder="Country" value="USA">
                                                    </div>
                                                    <div class="col-xl-12">
                                                        <label for="text-area" class="form-label">Address</label>
                                                        <textarea class="form-control" id="text-area" rows="4"></textarea>
                                                        <div class="form-check mt-1">
                                                            <input class="form-check-input form-checked-outline form-checked-success" type="checkbox" value="" id="invalidCheck1" required="" checked="">
                                                            <label class="form-check-label text-success fs-12" for="invalidCheck1">
                                                                Same as Shipping Address Address ?
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="px-4 py-3 border-top border-block-start-dashed d-sm-flex justify-content-between">
                                                <button type="button" class="btn btn-danger-light m-1" id="back-shipping-trigger"><i class="ri-truck-line me-2 align-middle d-inline-block"></i>Back To Shipping</button>
                                                <button type="button" class="btn btn-success-light m-1" id="payment-trigger">Continue To Payment<i class="bi bi-credit-card-2-front align-middle ms-2 d-inline-block"></i></button>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade border-0 p-0" id="shipped-tab-pane" role="tabpanel" aria-labelledby="shipped-tab-pane" tabindex="0">
                                            <div class="p-4">
                                                <p class="mb-1 fw-semibold text-muted op-5 fs-20">03</p>
                                                <div class="fs-15 fw-semibold d-sm-flex d-block align-items-center justify-content-between mb-3">
                                                    <div>Payment Details :</div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-xl-12">
                                                        <div class="mb-3">
                                                            <label class="form-label">Delivery Address</label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" placeholder="Address" aria-label="address" aria-describedby="payment-address" value="MIG-1-11,Monroe Street,Washington D.C,USA">
                                                                <button type="button" class="btn btn-info-light input-group-text" id="payment-address">Change</button>
                                                            </div>
                                                        </div>
                                                        <div class="card custom-card border shadow-none mb-3">
                                                            <div class="card-header">
                                                                <div class="card-title">
                                                                    Payment Methods
                                                                </div>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="btn-group mb-3 d-sm-flex d-block" role="group" aria-label="Basic radio toggle button group">
                                                                    <input type="radio" class="btn-check" name="btnradio" id="btnradio1">
                                                                    <label class="btn btn-outline-light text-default mt-sm-0 mt-1" for="btnradio1">C.O.D(Cash on delivery)</label>
                                                                    <input type="radio" class="btn-check" name="btnradio" id="btnradio2">
                                                                    <label class="btn btn-outline-light text-default mt-sm-0 mt-1" for="btnradio2">UPI</label>
                                                                    <input type="radio" class="btn-check" name="btnradio" id="btnradio3" checked="">
                                                                    <label class="btn btn-outline-light text-default mt-sm-0 mt-1" for="btnradio3">Credit/Debit Card</label>
                                                                </div>
                                                                <div class="row gy-3">
                                                                    <div class="col-xl-12">
                                                                        <label for="payment-card-number" class="form-label">Card Number</label>
                                                                        <input type="text" class="form-control" id="payment-card-number" placeholder="Card Number" value="1245 - 5447 - 8934 - XXXX">
                                                                    </div>
                                                                    <div class="col-xl-12">
                                                                        <label for="payment-card-name" class="form-label">Name On Card</label>
                                                                        <input type="text" class="form-control" id="payment-card-name" placeholder="Name On Card" value="JSON TAYLOR">
                                                                    </div>
                                                                    <div class="col-xl-4">
                                                                        <label for="payment-cardexpiry-date" class="form-label">Expiration Date</label>
                                                                        <input type="text" class="form-control" id="payment-cardexpiry-date" placeholder="MM/YY" value="08/2024">
                                                                    </div>
                                                                    <div class="col-xl-4">
                                                                        <label for="payment-cvv" class="form-label">CVV</label>
                                                                        <input type="text" class="form-control" id="payment-cvv" placeholder="XXX" value="341">
                                                                    </div>
                                                                    <div class="col-xl-4">
                                                                        <label for="payment-security" class="form-label">O.T.P</label>
                                                                        <input type="text" class="form-control" id="payment-security" placeholder="XXXXXX" value="183467">
                                                                        <label for="payment-security" class="form-label mt-1 text-success fs-11"><sup><i class="ri-star-s-fill"></i></sup>Do not share O.T.P with anyone</label>
                                                                    </div>
                                                                    <div class="col-xl-12">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input form-checked-success" type="checkbox" value="" id="payment-card-save" checked="">
                                                                            <label class="form-check-label" for="payment-card-save">
                                                                                Save this card
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="card-footer">
                                                                <div class="row gy-3">
                                                                    <p class="fs-15 fw-semibold mb-1">Saved Cards :</p>
                                                                    <div class="col-xl-6">
                                                                        <div class="form-check payment-card-container mb-0 lh-1">
                                                                            <input id="payment-card1" name="payment-cards" type="radio" class="form-check-input" checked="">
                                                                            <div class="form-check-label">
                                                                               <div class="d-sm-flex d-block align-items-center justify-content-between">
                                                                                   <div class="me-2 lh-1">
                                                                                       <span class="avatar avatar-md">
                                                                                           <img src="../assets/images/ecommerce/png/26.png" alt="">
                                                                                       </span>
                                                                                   </div>
                                                                                   <div class="saved-card-details">
                                                                                       <p class="mb-0 fw-semibold">XXXX - XXXX - XXXX - 7646</p>
                                                                                   </div>
                                                                               </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-xl-6">
                                                                        <div class="form-check payment-card-container mb-0 lh-1">
                                                                            <input id="payment-card2" name="payment-cards" type="radio" class="form-check-input">
                                                                            <div class="form-check-label">
                                                                               <div class="d-sm-flex d-block align-items-center justify-content-between">
                                                                                   <div class="me-2 lh-1">
                                                                                       <span class="avatar avatar-md">
                                                                                           <img src="../assets/images/ecommerce/png/27.png" alt="">
                                                                                       </span>
                                                                                   </div>
                                                                                   <div class="saved-card-details">
                                                                                       <p class="mb-0 fw-semibold">XXXX - XXXX - XXXX - 9556</p>
                                                                                   </div>
                                                                               </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="px-4 py-3 border-top border-block-start-dashed d-sm-flex justify-content-between">
                                                <button type="button" class="btn btn-danger-light m-1" id="back-personal-trigger"><i class="ri-user-3-line me-2 align-middle d-inline-block"></i>Back To Personal Info</button>
                                                <button type="button" class="btn btn-success-light m-1" id="continue-payment-trigger">Continue Payment<i class="bi bi-credit-card-2-front align-middle ms-2 d-inline-block"></i></button>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade border-0 p-0" id="delivery-tab-pane" role="tabpanel" aria-labelledby="delivery-tab-pane" tabindex="0">
                                            <div class="p-5 checkout-payment-success my-3">
                                                <div class="mb-5">
                                                    <h5 class="text-success fw-semibold">Payment Successful...🤝</h5>
                                                </div>
                                                <div class="mb-5">
                                                    <img src="../assets/images/ecommerce/png/24.png" alt="" class="img-fluid">
                                                </div>
                                                <div class="mb-4">
                                                    <p class="mb-1 fs-14">You can track your order with Order Id <b>SPK#1FR</b> from <a class="link-success" href="javascript:void(0);"><u>Track Order</u></a></p>
                                                    <p class="text-muted">Thankyou for shopping with us.</p>
                                                </div>
                                                <a href="products.html" class="btn btn-success">Continue Shopping<i class="bi bi-cart ms-2"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
        </div>
    </div>

@endsection

@section('select2')
    <style>
        /* Fondo transparente y sin bordes */
    .select2-container .select2-selection--single {
    background-color: transparent !important;
    border: none !important;
    box-shadow: none !important; /* Eliminar sombras */
    }


    .select2-container .select2-selection--single:focus {
    outline: none !important;
    }


    .select2-container .select2-selection--single .select2-selection__rendered {
    color: inherit; /* Heredar color del texto */
    background-color: transparent !important;
    }
    </style>
    <script src="{{ asset('assets/vendor/jquery/dist/jquery.min.js')}}"></script>
    <script src="{{ asset('assets/vendor/select2/dist/js/select2.min.js')}}"></script>
    <script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
    <script src="{{ asset('js/sgt/cotizaciones/cotizaciones.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizaciones.js')) }}"></script>

    <script type="text/javascript">
    $(document).ready(function() {
    $('.cliente').select2();
    });
    </script>

    <script type="text/javascript">
        // ============= Agregar mas inputs dinamicamente =============
        $('.clonar').click(function() {
        // Clona el .input-group
        var $clone = $('#formulario .clonars').last().clone();

        // Borra los valores de los inputs clonados
        $clone.find(':input').each(function () {
            if ($(this).is('select')) {
            this.selectedIndex = 0;
            } else {
            this.value = '';
            }
        });

        // Agrega lo clonado al final del #formulario
        $clone.appendTo('#formulario');
        });

    </script>

    <script>
        // ============= Agregar mas inputs dinamicamente =============
        $('.clonar2').click(function() {
        // Clona el .input-group
        var $clone = $('#formulario2 .clonars2').last().clone();

        // Borra los valores de los inputs clonados
        $clone.find(':input').each(function () {
            if ($(this).is('select')) {
            this.selectedIndex = 0;
            } else {
            this.value = '';
            }
        });

        // Agrega lo clonado al final del #formulario2
        $clone.appendTo('#formulario2');
        });
    </script>

    <script>
         document.addEventListener('DOMContentLoaded', function () {
            // Obtener referencias a los elementos
            var optionSi = document.getElementById('option_si_ccp');
            var optionNo = document.getElementById('option_no_ccp');
            var inputFieldIMG = document.getElementById('inputFieldccp');

            // Función para controlar la visibilidad del campo de entrada
            function toggleInputField() {
                // Si el radio button "Sí" está seleccionado, mostrar el campo de entrada
                if (optionSi.checked) {
                    inputFieldIMG.style.display = 'block';
                } else {
                    inputFieldIMG.style.display = 'none';
                }
            }

            // Agregar eventos change a los radio buttons
            optionSi.addEventListener('change', toggleInputField);
            optionNo.addEventListener('change', toggleInputField);

            // Llamar a la función inicialmente para asegurarse de que el campo se oculte o muestre correctamente
            toggleInputField();
        });

        document.addEventListener('DOMContentLoaded', function () {
            // Obtener referencias a los elementos
            var optionSi = document.getElementById('option_si');
            var optionNo = document.getElementById('option_no');
            var inputField = document.getElementById('inputField');
            var inputFieldIMG = document.getElementById('inputFieldIMG');

            // Función para controlar la visibilidad del campo de entrada
            function toggleInputField() {
                // Si el radio button "Sí" está seleccionado, mostrar el campo de entrada
                if (optionSi.checked) {
                    inputField.style.display = 'block';
                    inputFieldIMG.style.display = 'block';
                } else {
                    inputField.style.display = 'none';
                    inputFieldIMG.style.display = 'none';
                }
            }

            // Agregar eventos change a los radio buttons
            optionSi.addEventListener('change', toggleInputField);
            optionNo.addEventListener('change', toggleInputField);

            // Llamar a la función inicialmente para asegurarse de que el campo se oculte o muestre correctamente
            toggleInputField();
        });

        document.addEventListener('DOMContentLoaded', function () {
            // Obtener referencias a los elementos
            var eirSi = document.getElementById('eir_si');
            var eirNo = document.getElementById('eir_no');
            var inputEir = document.getElementById('inputEir');
            var inputEirFecha = document.getElementById('inputEirFecha');

            // Función para controlar la visibilidad del campo de entrada
            function toggleInputEir() {
                // Si el radio button "Sí" está seleccionado, mostrar el campo de entrada
                if (eirSi.checked) {
                    inputEir.style.display = 'block';
                    inputEirFecha.style.display = 'block';
                } else {
                    inputEir.style.display = 'none';
                    inputEirFecha.style.display = 'none';
                }
            }

            // Agregar eventos change a los radio buttons
            eirSi.addEventListener('change', toggleInputEir);
            eirNo.addEventListener('change', toggleInputEir);

            // Llamar a la función inicialmente para asegurarse de que el campo se oculte o muestre correctamente
            toggleInputEir();
        });

        $(document).ready(function() {
            function loadSubclientes(clienteId, selectedSubclienteId = null) {
                if (clienteId) {
                    $.ajax({
                        type: 'GET',
                        url: '/subclientes/' + clienteId,
                        success: function(data) {
                            $('#id_subcliente').empty();
                            $('#id_subcliente').append('<option value="">Seleccionar subcliente</option>');
                            $.each(data, function(key, subcliente) {
                                if (selectedSubclienteId && selectedSubclienteId == subcliente.id) {
                                    $('#id_subcliente').append('<option value="' + subcliente.id + '" selected>' + subcliente.nombre + '</option>');
                                } else {
                                    $('#id_subcliente').append('<option value="' + subcliente.id + '">' + subcliente.nombre + '</option>');
                                }
                            });
                            $('#id_subcliente').select2()
                        }
                    });
                } else {
                    $('#id_subcliente').empty();
                    $('#id_subcliente').append('<option value="">Seleccionar subcliente</option>');
                }
            }

            $('#id_cliente').change(function() {
                var clienteId = $(this).val();
                loadSubclientes(clienteId);
            });

            // Load subclientes on page load
            var initialClienteId = $('#id_cliente').val();
            var initialSubclienteId = '{{ $cotizacion->id_subcliente }}';
            loadSubclientes(initialClienteId, initialSubclienteId);
        });
    </script>

    

    <script>
        $(document).ready(()=>{
            
            calcularTotal()

            formFields.forEach((item) =>{
                if(item.type == "money") {
                    var field = document.getElementById(item.field);
                    field.value =  (field.value.length > 0) ? reverseMoneyFormat(field.value) : 0
                    field.value = moneyFormat(field.value || 0);
                }
            });

            formFieldsProveedor.forEach((item) =>{
                if(item.type == "money") {
                    var field = document.getElementById(item.id);
                    if(field){
                      field.value = (field.value.length > 0) ? reverseMoneyFormat(field.value) : 0
                      field.value = moneyFormat(field.value || 0);
                    }
                   
                }
            });

           
        })
    </script>

<script>
        document.addEventListener('DOMContentLoaded', function () {
            let condicionRecinto = document.querySelectorAll('.recinto');
            let inputRecinto = document.querySelector('#input-recinto');
            let textRecinto = document.querySelector('#text_recinto');

            condicionRecinto.forEach(function(elemento) {
              //  elemento.classList.remove('active')
                elemento.addEventListener('click', function() {
                    inputRecinto.classList.toggle('d-none',elemento.attributes['data-kt-plan'].value != 'recinto-si') 
                    textRecinto.value = (elemento.attributes['data-kt-plan'].value != 'recinto-si') ? '' : 'recinto-si';
                });
                
          
              
               //elemento.classList.toggle('active',elemento.attributes['data-kt-plan'].value == 'recinto-si' && '{{$cotizacion->uso_recinto}}' == 1) 


            });
        });

    </script>

@endsection
