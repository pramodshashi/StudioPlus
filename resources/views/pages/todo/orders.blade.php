@extends('layouts.app')
@include('components.orderviewmodal_All')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@section('content')
<!-- <div class='s-page-title'>Orders</div> -->
<div style='background-color: lightgrey; width: 90%; border: 2px solid green;padding-left:1%; margin-top: 1%; margin-right: 5%;margin-left: 5%;border-radius:1%;'>
    <!-- Multiple Radios -->
    <div class="customer-section">
    </div>

    <!-- Order Details Form -->
    <form id="orderDetailsForm" class="form-horizontal" style="height: 600px;">
        <fieldset>
            <div style="display:flex">
                <div class="section_logo"><img width="30px" height="30px" src="{{ asset('images/order.png') }}"/></div>
                <div class="section_title">Order Information</div>
            </div>
            <div style="display:inline-flex;padding-top: 15px;gap: 75px;">
                <form id="order_search">
                    <div class="col-md-4">
                        <label class="form-label" for="otype">Order Type (*)</label>
                        <select style="margin-top: -7px;" id="search-otype" name="search-otype" value="1" class="form-control">

                            @foreach ($orderTypes as $orderType)
                                <option value="{{ $orderType->ordertypekey }}">{{ $orderType->ordertype }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- <div class="col-md-4" id="Sittings">
                        <label class="col-md-4 control-label" >Item</label>
                        <select id="sitting_item" name="item" class="form-control" style="width: 80%;">
                            <option value="0">ALL ITEMS</option>
                            <option value="1">Passport</option>
                            <option value="2">NIC</option>
                            <option value="3">Stamp</option>
                        </select>
                    </div> -->
                    <div class="col-md-4" id="cname">
                            <label class="col-md-4 control-label" for="name" >Search Text</label>
                            <input id="search-term" name="username" style="width: 80%;" type="text" placeholder="Customer Name,Phone or Order No." class="form-control">
                            <span class="error-message text-danger" id="username-error"></span>

                    </div>
                    <!-- <div class="col-md-4">
                        <label _class="col-md-4 control-label">Delivery date-within</label>
                        <input class="form-control input-md" type="date" id="search-stdate" name="search-stdate" value="">
                        <input class="form-control input-md" type="date" id="search-enddate" name="search-enddate">
                    </div> -->
                    <div class="form-group">
                        <label class="form-label">Order Create Date (Within)</label>
                        <div style="display: flex; gap: 10px;">
                            <input class="form-control" type="date" id="search-stdate" name="search-stdate">
                            <input class="form-control" type="date" id="search-enddate" name="search-enddate">
                        </div>
                    </div>
                    <div class="col-md-4" style="padding-top: 30px;margin-left: 35px;">
                        <button type="submit" onClick="loaddata()" id="searchBtn" class="btn btn-primary">Go</button>
                    </div>

                </Form>
            </div></br></br>
            <div>
                <div><span id="ocount"></span> order(s)</div>
                <div style="background-color:#aaa;height: 350px;overflow-y: auto;" id="orderResults">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Order No.</th>
                                <th>Order Type</th>
                                <th>Date Time</th>
                                <th>Urgent</th>
                                <th>Created At</th>
                                <th>Total Cost (LKR)</th>
                                <th>Total Paid (LKR)</th>
                                <th>Comments</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="order-summary">
                            <!-- Orders will be dynamically added here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </fieldset>
    </form>
</div>

@push('scripts')
<script>
    let orderUpdated = false;
    window.onload = function() {
        loaddata()
    };
    // Set default values
    document.getElementById("search-stdate").value = getFormattedDate(-30);
    document.getElementById("search-enddate").value = getFormattedDate(+1); // Today


    function loaddata()
    {
        //document.getElementById('ocount').innerHTML='loading...'
        let otype = $('#search-otype').val();
        let query = $('#search-term').val();
        let startDate = $('#search-stdate').val();
        let endDate = $('#search-enddate').val();
        event.preventDefault(); // Prevent default form submission
        $('#orderResults').html("");
        $.ajax({
            url: "/orders/search",
            type: "POST",
            data: {
                query: query,
                otype: otype,
                start_date: startDate,
                end_date: endDate,
                _token: "{{ csrf_token() }}" // CSRF Token for security
            },
            success: function (response) {
                //if (response.status) {
                    console.log('search result',response)
                    displayOrderSearchResults(response);

            }
        });

        setTimeout(function () {
        loaddata();
        }, 500); // 500ms delay ensures proper execution           // Trigger button click on page load

    }

    function displayOrderSearchResults(orders)
    {

        if (!orders.length) {
            $('#orderResults').html(
                '<div class="alert alert-info">No Orders found.</div>'
            );
            document.getElementById('ocount').innerHTML=0
            return;
        }
        document.getElementById('ocount').innerHTML=orders.length

        let html = `
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Order No.</th>
                        
                        <th>Date Time</th>
                        <th>Customer Name</th>
                        <th>Urgent</th>
                        <th>Total Cost (LKR)</th>
                        <th>Discount(%)</th>
                        <th>Paid Amt (LKR)</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
            <tbody>
        `;

        orders.forEach(function(order) {

            html += `
                <tr>
                    <td>${order.orderid}</td>
                    
                    <td>${order.createdtime}</td>
                    <td>${order.username}</td>
                    <td>${order.urgent_flag === 1 ? 'Yes' : 'No'}</td>
                    <td>${order.totalcost}</td>
                    <th>${order.discount}</th>
                    <td>${order.paidcost}</td>
                    <td>${order.salestatus}</td>
                    <td>
                        <button onClick="vieworder(${order.orderkey},'${order.orderid}','${order.createdtime}','${order.username}','${order.totalcost}','${order.discount}','${order.paidcost}','${order.urgent_flag === 1 ? 'Yes' : 'No'}','${order.salestatus}',${order.ordertypekey},'${order.ordertype}')" id="vieword" _data-orderkey="${order.orderkey}" type="button" class="btn btn-primary view-order"  >
                        View
                        </button>

                    </td>
                </tr>
            `;
        });

        html += '</tbody></table>';
        $('#orderResults').html(html);
    }

    function getFormattedDate(offset = 0) {
        let date = new Date();
        date.setDate(date.getDate() + offset); // Add offset days
        return date.toISOString().split('T')[0]; // Convert to YYYY-MM-DD
    }

    // ******************* View Bill Related Orders **********************

    function loaditemdata(okey){    
        loaditemdata_FR(okey,'Frames')
        loaditemdata_ME(okey,'Media')
        loaditemdata_EC(okey,'Extra Copy')
        loaditemdata_SS(okey,'Studio Sittings')
        orderTotalSummary(okey)
    }
    
    //*********************Order Total Summary **********************

    function orderTotalSummary(orderKey){
        $.ajax({
            url: "/order-totalsummary/" + orderKey ,
            type: "GET",
            success: function (response) {
                if (response.status==='success') {

                    let orderSummaryTotalHtml = "";
                    let orderTotalCost = 0;
                    let orderDiscount = 0;
                    let discountAmount = 0;
                    let paidAmount = 0;


                        orderTotalCost += parseFloat(response.grandtotal) || 0;
                        orderDiscount = parseFloat(response.dicount) || 0;
                        paidAmount = parseFloat(response.paidamount) || 0;



                    discountAmount = (orderTotalCost * orderDiscount) / 100;
                    let balanceDue = (orderTotalCost - discountAmount) - paidAmount;

                    orderSummaryTotalHtml = `
                        <tr>
                            <th style="width: 50%;">Total Cost</th>
                            <td><span id="total-cost">Rs ${orderTotalCost.toFixed(2)}</span></td>
                        </tr>
                        <tr>
                            <th>Discount (${orderDiscount}%)</th>
                            <td><span id="total-cost">Rs ${discountAmount.toFixed(2)}</span></td>
                        </tr>
                        <tr>
                            <th>Paid Amount</th>
                            <td><span id="total-cost">Rs ${paidAmount.toFixed(2)}</span></td>
                        </tr>
                        <tr>
                            <th>Balance Due</th>
                            <td><span id="balance-due" style="font-weight:700;">Rs ${balanceDue.toFixed(2)}</span></td>
                        </tr>`;


                    $("#ordersummary").html(orderSummaryTotalHtml);
                }
            },
            error: function (xhr) {
                console.error("Error fetching order summary:", xhr);
            }
        });
    }


    // ******************* Studio sitting Retated ************************

    function loaditemdata_SS(okey,orderType){
        let orderkey = okey;
        $.ajax({
            url: "/order-itemsummary/" + orderkey + "?ordertype=" + encodeURIComponent(orderType),
            type: "GET",
            data: {
                orderkey: orderkey,
                _token: "{{ csrf_token() }}" // CSRF Token for security
            },
            success: function (response) {
                console.log('Server response:', response); // Debugging

                if (!response || response.length === 0) {
                    console.log("No order items returned from server.");
                } else {
                    displayOrderitemSearchResults(response,orderkey,orderType);
                }
            }
        });

    }

    function displayOrderitemSearchResults(orderitems,orderkey,orderType) {
        if (!orderitems.orderItems.length) {
            $('#orderitemResults').html(
                // '<div class="alert alert-info">No Order Items found.</div>'
            );
            return;
        }

        //show order summary
        // $.ajax({
        //     url: "/order-itemsummary/" + orderkey + "?ordertype=" + encodeURIComponent(orderType),
        //     type: "GET",
        //     success: function (response) {
        //         if (response.status === "success") {
        //             let orderSummaryHtml = "";
        //             let orderSummaryTotalHtml = "";
        //             let orderTotalCost = 0;
        //             let orderDiscount = 0;
        //             let discountAmount = 0;
        //             let paidAmount = 0;

        //             response.orderItems.forEach(item => {
        //                 orderTotalCost += parseFloat(item.totalcost) || 0;
        //                 orderDiscount = parseFloat(item.order.discount) || 0;
        //                 paidAmount = parseFloat(item.order.paidcost) || 0;
        //             });

        //             discountAmount = (orderTotalCost * orderDiscount) / 100;
        //             let balanceDue = (orderTotalCost - discountAmount) - paidAmount;

        //             orderSummaryTotalHtml = `
        //             </br><table>
        //                 <tr>
        //                     <th style="width: 50%;">Total Cost</th>
        //                     <td><span id="total-cost">Rs ${orderTotalCost.toFixed(2)}</span></td>
        //                 </tr>
        //                 <tr>
        //                     <th>Discount (${orderDiscount}%)</th>
        //                     <td><span id="total-cost">Rs ${discountAmount.toFixed(2)}</span></td>
        //                 </tr>
        //                 <tr>
        //                     <th>Paid Amount</th>
        //                     <td><span id="total-cost">Rs ${paidAmount.toFixed(2)}</span></td>
        //                 </tr>
        //                 <tr>
        //                     <th>Balance Due</th>
        //                     <td><span id="balance-due" style="font-weight:700;">Rs ${balanceDue.toFixed(2)}</span></td>
        //                 </tr>
        //             </table> `;

        //             $("#ordersummary").html(orderSummaryTotalHtml);
        //         }
        //     },
        //     error: function (xhr) {
        //         console.error("Error fetching order summary:", xhr);
        //     }
        // });

        // end order summary
        let html = `
            <div style="margin-bottom: 10px;">
                <h5 style="margin-bottom:10px;">Order Type: <strong>${orderType}</strong></h5>
                <button id="addnew" onClick="addnew_ss('${orderkey}', '${orderType}')" type="button" class="btn btn-info" style="font-size:15px;">+ Add New Item</button>
            </div>
            <table id="itemtable" class="table table-bordered">
                <thead>
                    <tr id="row_0">
                        <th>Item Type</th>
                        <th>Hard Copied</th>
                        <th>Soft Copies</th>
                        <th>Edit Type</th>
                        <th>Cost (LKR)</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
        `;

        console.log('Order Item',orderitems)

        orderitems.orderItems.forEach(function(orderitem) {

            const isCompleted = orderitem.iscompleted === 1;
            const doneBtnClass = isCompleted ? 'btn-done' : 'btn-secondary';
            const doneBtnStyle = isCompleted ? '' : 'style="background-color: gray;"';

            html += `
                <tr id="row_${orderitem.ssorderitemmapkey}">
                    <td id="name_${orderitem.ssorderitemmapkey}">${orderitem.order_type_item.itemname}</td>
                    <td id="hcopy_${orderitem.ssorderitemmapkey}">${orderitem.hardcopyquantity}</td>
                    <td id="scopy_${orderitem.ssorderitemmapkey}">${orderitem.softcopyquantity}</td>
                    <td id="edittype_${orderitem.ssorderitemmapkey}">${orderitem.edit_type.edittype}</td>
                    <td>${orderitem.totalcost}</td>
                    <td>${orderitem.iscompleted === 1 ? 'Completed' : 'In Progress'}</td>


                    <td id="tdeditBtn_${orderitem.ssorderitemmapkey}">
                        <div class="d-flex justify-content-start gap-1">
                            <button id="doneBtn_${orderitem.ssorderitemmapkey}"
                                type="button"
                                class="btn done-item ${doneBtnClass} text-white mark-done-btn"
                                data-id="${orderitem.ssorderitemmapkey}"
                                data-orderid="${orderitem.orderkey}"
                                title="Mark as Complete"
                                ${doneBtnStyle}>
                                <i class="fas fa-check"></i>
                            </button>

                            <button id="editBtn_${orderitem.ssorderitemmapkey}" type="button" title="Edit" class="btn btn-edit edit-order" onClick="edititem(${orderitem.ssorderitemmapkey},${orderitem.hardcopyquantity},${orderitem.softcopyquantity},'${orderitem.edit_type.edittype}')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button style="display:none" id="saveBtn_${orderitem.ssorderitemmapkey}"
                                type="button" class="btn btn-save save-order"title="Save"
                                onClick="saveitem('${orderitem.jobid}',${orderitem.ssorderitemmapkey},'${orderitem.order.order_type.ordertype}',${orderitem.order.order_type.ordertypekey},${orderitem.order_type_item.ordertypeitemkey},'${orderitem.lam_type?.lamtypekey || ''}',${orderitem.order.customerkey},${orderitem.order.isurgent},'${orderitem.order.discount}','${orderitem.order.paidcost}',${orderitem.order.studiokey},'${orderitem.order.orderid}','${orderitem.order.deliverydate}','${orderitem.order.remarks}')">
                                <i class="fas fa-save"></i>
                            </button>
                            <button id="dltBtn_${orderitem.ssorderitemmapkey}" class="btn btn-delete remove-order" data-orderid="${orderitem.orderkey}" data-id="${orderitem.ssorderitemmapkey}" data-type="${orderitem.order.order_type.ordertype}"><i class="fas fa-trash"></i></button>
                        </dev>
                    </td>

                </tr>
            `;
        });

        html += '</tbody></table>';
        $('#orderitemResults').html(html);
    }

    function edititem(ssorderitemmapkey) {
        // Get latest values from the table before editing
        let hcopyElement = document.getElementById(`hcopy_${ssorderitemmapkey}`);
        let scopyElement = document.getElementById(`scopy_${ssorderitemmapkey}`);
        let edittypeElement = document.getElementById(`edittype_${ssorderitemmapkey}`);

        // Ensure elements exist before accessing properties
        if (!hcopyElement || !scopyElement || !edittypeElement) {
            console.error(`Error: One or more elements missing for item ${ssorderitemmapkey}`);
            return;
        }

        let hcopy = hcopyElement.textContent.trim();
        let scopy = scopyElement.textContent.trim();
        let edittype = edittypeElement.textContent.trim(); // Get displayed edit type text

        // Hide Edit Button, Show Save Button
        document.getElementById(`editBtn_${ssorderitemmapkey}`).style.display = "none";
        document.getElementById(`dltBtn_${ssorderitemmapkey}`).style.display = "none";
        document.getElementById(`doneBtn_${ssorderitemmapkey}`).style.display = "none";
        document.getElementById(`saveBtn_${ssorderitemmapkey}`).style.display = "inline-block";

        // Convert Hard Copy to Input Field
        hcopyElement.innerHTML =
            `<div class="col-md-4">
                <input style="border-color: orange;" id="input_hcopy_${ssorderitemmapkey}" value="${hcopy}" name="hcopy" type="text" class="form-control input-md" required="">
            </div>`;

        // Convert Soft Copy to Input Field
        scopyElement.innerHTML =
            `<div class="col-md-4">
                <input style="border-color: orange;" id="input_scopy_${ssorderitemmapkey}" value="${scopy}" name="scopy" type="text" class="form-control input-md" required="">
            </div>`;

        // Convert Edit Type to Dropdown
        edittypeElement.innerHTML =
            `<div class="col-md-4">
                <select style="width:150px;border-color: orange;" id="input_edittype_${ssorderitemmapkey}" name="edittype" class="form-control">
                    <option value="">Select Edit Type</option>
                    @foreach ($editTypes as $editType)
                        <option value="{{ $editType->edittypekey }}" ${edittype === '{{ $editType->edittype }}' ? 'selected' : ''}>{{ $editType->edittype }}</option>
                    @endforeach
                </select>
            </div>`;
    }

    function saveitem(jobid,ssorderitemmapkey, ordertype, ordertypekey, ordertypeitemkey, lamtypekey, customerkey, isurgent, discount, paidcost, studiokey, orderid, deliverydate, remarks) {
        dataarray=[]

        setTimeout(() => {
            let hcopy = document.querySelector(`#input_hcopy_${ssorderitemmapkey}`)?.value || "";
            let scopy = document.querySelector(`#input_scopy_${ssorderitemmapkey}`)?.value || "";
            let edittypeElement = document.querySelector(`#input_edittype_${ssorderitemmapkey}`);
            if (!edittypeElement) {
                console.error("Edit type dropdown not found!");
                return;
            }
            let edittype = edittypeElement.options[edittypeElement.selectedIndex].value;
            let edittypeText = edittypeElement.options[edittypeElement.selectedIndex].text;

            if (!edittype) {
                console.error("Error: edittype is not defined or empty!");
                return; // Prevent the function from executing further if edittype is missing.
            }

            console.log("Hard Copy:", hcopy);
            console.log("Soft Copy:", scopy);
            console.log("Edit Type:", edittype);
            let dataarray = {
                jobid:jobid,
                studiokey: studiokey,
                orderid: orderid,
                ordertypekey: ordertypekey,
                ordertype: ordertype,
                ordertypeitemkey: ordertypeitemkey,
                edittypekey: edittype,
                lamtypekey: lamtypekey,
                customerkey: customerkey,
                isurgent: isurgent,
                discount: discount,
                paidcost: paidcost,
                softcopycount: scopy,  // Fix: Use correct variable
                hardcopycount: hcopy,   // Fix: Use correct variable
                deliverydate: deliverydate,
                remarks: remarks,
                iscompleted: 0,
                _token: "{{ csrf_token() }}" // Required for Laravel AJAX requests
            };
            console.log('Data Sent:', dataarray);

            let cells = document.querySelectorAll(`#row_${ssorderitemmapkey} td`);
            cells.forEach(cell => {
                if (cell.cellIndex !== 0) {
                    cell.contentEditable = "false";
                    cell.classList.remove("edit-mode");
                }
            });

            $.ajax({
                    url: "{{ route('storeOrder_ss') }}",
                    type: "POST",
                    data: dataarray,
                    success: function (response) {
                        console.log("Order Updated Successfully:", response);

                        // Update the table row with latest values
                        document.getElementById(`hcopy_${ssorderitemmapkey}`).innerHTML = hcopy;
                        document.getElementById(`scopy_${ssorderitemmapkey}`).innerHTML = scopy;
                        document.getElementById(`edittype_${ssorderitemmapkey}`).innerHTML = edittypeText;

                        // Show Edit Button Again
                        document.getElementById(`editBtn_${ssorderitemmapkey}`).style.display = "inline-block";
                        document.getElementById(`dltBtn_${ssorderitemmapkey}`).style.display = "inline-block";
                        document.getElementById(`doneBtn_${ssorderitemmapkey}`).style.display = "inline-block";
                        document.getElementById(`saveBtn_${ssorderitemmapkey}`).style.display = "none";
                        // render table
                        let flashbody = '<div id="flash-message" class="alert alert-success" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);z-index: 9999; padding: 15px 20px; font-size: 16px; text-align: center;background-color: #434844; color: white; border-radius: 5px; box-shadow: 0px 4px 6px rgba(0,0,0,0.1);">'
                        + response.message +'!</div>';

                        $("body").prepend(flashbody);
                            // Automatically remove the message after 2 seconds
                            setTimeout(function() {
                                $("#flash-message").fadeOut("slow", function() {
                                    $(this).remove();
                                });
                            }, 2000);
                    },
                    error: function (xhr, status, error) {
                    console.error("Error:", xhr.responseText);

                    // Attempt to parse the JSON response
                    try {
                        var response = JSON.parse(xhr.responseText);

                        // Display the error message using SweetAlert2
                        Swal.fire({
                            title: 'Failed to Create Order',
                            text: response.message || 'An unexpected error occurred.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } catch (e) {
                        // If parsing fails, display a generic error message
                        Swal.fire({
                            title: 'Failed to Create Order',
                            text: 'An unexpected error occurred.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        }, 300);
    }

    function addnew_ss(orderkey,orderType){
        if (!orderkey) {
            alert("Order key is missing!");
            return;
        }
        // Get the table body
        let table = document.getElementById("itemtable").getElementsByTagName('tbody')[0];

        // Create a new row
        let newRow = table.insertRow();
        newRow.style.backgroundColor = "lightblue";

        // Insert cells into the row
        let itemcell = newRow.insertCell(0);
        let hcopycell = newRow.insertCell(1);
        let scopycell = newRow.insertCell(2);
        let edittypecell = newRow.insertCell(3);
        let costcell = newRow.insertCell(4);
        let statuscell = newRow.insertCell(5);
        let actioncell = newRow.insertCell(6);

        // Get order type key
        var otk = document.getElementById("otk")?.value || "";

        // Add content to the new cells
        itemcell.innerHTML = `
            <div style="width:150px;border-color: blue;border-width: 2px;" class="col-md-4">
                <select id="sittingitem" name="item" class="form-control">
                    <option value="">Select Item Type</option>
                </select>
            </div>
        `;

        setTimeout(() => loadOrderTypeItems(otk), 300);

        hcopycell.innerHTML = `
            <div class="col-md-4">
                <input id="hcopy" name="hcopy" type="text" class="form-control input-md" required="">
            </div>
        `;

        scopycell.innerHTML = `
            <div class="col-md-4">
                <input id="scopy" name="scopy" type="text" class="form-control input-md" required="">
            </div>
        `;

        edittypecell.innerHTML = `
            <div class="col-md-4">
                <select style="width:150px;" id="edittype" name="edittype" class="form-control">
                    <option value="">Select Edit Type</option>
                    @foreach ($editTypes as $editType)
                        <option value="{{ $editType->edittypekey }}">{{ $editType->edittype }}</option>
                    @endforeach
                </select>
            </div>
        `;

        costcell.innerHTML = '';
        statuscell.innerHTML = '';

        actioncell.innerHTML = `
            <button id="addBtn" type="button" class="btn btn-primary" onClick="additem(this)" data-orderkey="${orderkey}" data-ordertype="${orderType}">
                Add
            </button>
        `;
        document.getElementById("addBtn").style.display = "inline-block";
    }

    function additem(btn) {
        let orderkey = btn.getAttribute("data-orderkey");
        let orderType = btn.getAttribute("data-ordertype");
        alert(orderType)
        // Get the row (parent of the button)
        let row = btn.closest("tr");

        // Extract input values
        let hcopy = row.querySelector("input[name='hcopy']").value;
        let scopy = row.querySelector("input[name='scopy']").value;
        let edittype = row.querySelector("select[name='edittype']").value;
        let item = row.querySelector("select[name='item']").value;

        $.ajax({
            url: "/order-itemsummary/" + orderkey + "?ordertype=" + encodeURIComponent(orderType),
            type: "GET",
            data: {
                orderkey: orderkey,
                hcopy: hcopy,
                scopy: scopy,
                edittype: edittype,
                item: item,
                _token: "{{ csrf_token() }}" // CSRF Token for security
            },
            success: function (response) {
                if (!response || response.length === 0) {
                    console.log("No order items returned from server.");
                } else {
                    console.log('data call working yyyyyyyyyy...............',response)
                    createitem_ss(response,{ orderkey, hcopy, scopy, edittype, item});
                }
            }
        });
    }

    function createitem_ss(orderdata,formData) {
        console.log('item create ss ...............',formData)
        if (!orderdata.orderItems.length) {
            $('#orderitemResults').html(
                // '<div class="alert alert-info">No Order Items found.</div>'
            );
            return;
        }

        let orderdetail=orderdata.orderItems[0];
        console.log('item create ss ...............',orderdetail)
        //dataarray=[]

        let dataarray = {
            jobid: orderdetail.jobid,
            studiokey: orderdetail.order.studiokey,
            orderid: orderdetail.order.orderid,
            ordertypekey: orderdetail.order.ordertypekey,
            ordertype: orderdetail.order.order_type.ordertype,
            ordertypeitemkey: formData.item,
            edittypekey: formData.edittype,
            lamtypekey: orderdetail.lamtypekey,
            customerkey: orderdetail.order.customerkey,
            isurgent: orderdetail.order.isurgent,
            discount: orderdetail.order.discount,
            paidcost: orderdetail.order.paidcost,
            softcopycount: formData.scopy,  // Fix: Use correct variable
            hardcopycount: formData.hcopy,   // Fix: Use correct variable
            deliverydate: orderdetail.order.deliverydate,
            remarks: orderdetail.order.remarks,
            iscompleted: 0,
            _token: "{{ csrf_token() }}" // Required for Laravel AJAX requests
        };

        $.ajax({
                url: "{{ route('storeOrder_ss') }}",
                type: "POST",
                data: dataarray,
                success: function (response) {
                    console.log("Order Item Created Successfully:", response);

                    setTimeout(() => {
                        loaditemdata_SS(formData.orderkey,orderdetail.order.order_type.ordertype);
                        $('orderviewmodal_All').modal('show');
                    }, 500);

                    document.getElementById("addBtn").style.display = "none";
                    // render table
                    let flashbody = '<div id="flash-message" class="alert alert-success" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);z-index: 9999; padding: 15px 20px; font-size: 16px; text-align: center;background-color: #434844; color: white; border-radius: 5px; box-shadow: 0px 4px 6px rgba(0,0,0,0.1);">'
                    + response.message +'!</div>';

                    $("body").prepend(flashbody);
                        // Automatically remove the message after 2 seconds
                        setTimeout(function() {
                            $("#flash-message").fadeOut("slow", function() {
                                $(this).remove();
                            });
                        }, 2000);
                },
                error: function (xhr, status, error) {
                console.error("Error:", xhr.responseText);

                // Attempt to parse the JSON response
                try {
                    var response = JSON.parse(xhr.responseText);

                    // Display the error message using SweetAlert2
                    Swal.fire({
                        title: 'Failed to Create Order',
                        text: response.message || 'An unexpected error occurred.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                } catch (e) {
                    // If parsing fails, display a generic error message
                    Swal.fire({
                        title: 'Failed to Create Order',
                        text: 'An unexpected error occurred.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            }
        });

    }

    // ******************* END Studio sitting Retated ************************

    // ******************* Media Related *****************************

    function loaditemdata_ME(okey,orderType){
        let orderkey = okey;
        $.ajax({
            url: "/order-itemsummary/" + orderkey + "?ordertype=" + encodeURIComponent(orderType),
            type: "GET",
            data: {
                orderkey: orderkey,
                _token: "{{ csrf_token() }}" // CSRF Token for security
            },
            success: function (response) {
                console.log('Server response:', response); // Debugging

                if (!response || response.length === 0) {
                    console.log("No order items returned from server.");
                } else {
                    displayOrderitemSearchResults_ME(response,orderkey,orderType);
                }
            }
        });

    }

    function displayOrderitemSearchResults_ME(orderitems,orderkey,orderType) {
        if (!orderitems.orderItems.length) {
            $('#orderitemResults_me').html(
                // '<div class="alert alert-info">No Order Items found.</div>'
            );
            return;
        }


            //show order summary
        //         $.ajax({
        //     url: "/order-itemsummary/" + orderkey + "?ordertype=" + encodeURIComponent(orderType),
        //     type: "GET",
        //     success: function (response) {
        //         if (response.status === "success") {
        //             let orderSummaryHtml = "";
        //             let orderSummaryTotalHtml = "";
        //             let orderTotalCost = 0;
        //             let orderDiscount = 0;
        //             let discountAmount = 0;
        //             let paidAmount = 0;

        //             response.orderItems.forEach(item => {
        //                 orderTotalCost += parseFloat(item.totalcost) || 0;
        //                 orderDiscount = parseFloat(item.order.discount) || 0;
        //                 paidAmount = parseFloat(item.order.paidcost) || 0;
        //             });

        //             discountAmount = (orderTotalCost * orderDiscount) / 100;
        //             let balanceDue = (orderTotalCost - discountAmount) - paidAmount;

        //             orderSummaryTotalHtml = `
        //             </br><table>
        //                 <tr>
        //                     <th style="width: 50%;">Total Cost</th>
        //                     <td><span id="total-cost">Rs ${orderTotalCost.toFixed(2)}</span></td>
        //                 </tr>
        //                 <tr>
        //                     <th>Discount (${orderDiscount}%)</th>
        //                     <td><span id="total-cost">Rs ${discountAmount.toFixed(2)}</span></td>
        //                 </tr>
        //                 <tr>
        //                     <th>Paid Amount</th>
        //                     <td><span id="total-cost">Rs ${paidAmount.toFixed(2)}</span></td>
        //                 </tr>
        //                 <tr>
        //                     <th>Balance Due</th>
        //                     <td><span id="balance-due" style="font-weight:700;">Rs ${balanceDue.toFixed(2)}</span></td>
        //                 </tr>
        //             </table> `;

        //             $("#ordersummary_me").html(orderSummaryTotalHtml);
        //         }
        //     },
        //     error: function (xhr) {
        //         console.error("Error fetching order summary:", xhr);
        //     }
        // });

        // end order summary
        let html = `
            <div style="margin-bottom: 10px;">
                <h5 style="margin-bottom:10px;">Order Type: <strong>${orderType}</strong></h5>
                <button id="addnew" onClick="addnew_me('${orderkey}')" type="button" class="btn btn-info" style="font-size:15px;">+ Add New Item</button>
            </div>
            <table id="itemtable_me" class="table table-bordered">
                <thead>
                    <tr id="row_0">
                        <th>Item Type</th>
                        <th>Laminate Type</th>
                        <th>Hard Copied</th>
                        <th>Edit Type</th>
                        <th>Cost (LKR)</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
        `;

        orderitems.orderItems.forEach(function(orderitem) {
            const isCompleted = orderitem.iscompleted === 1;
            const doneBtnClass = isCompleted ? 'btn-done' : 'btn-secondary';
            const doneBtnStyle = isCompleted ? '' : 'style="background-color: gray;"';

            html += `
                <tr id="row_me_${orderitem.meorderitemmapkey}">
                    <td id="name_me_${orderitem.meorderitemmapkey}">${orderitem.order_type_item.itemname}</td>
                    <td id="lamtype_me_${orderitem.meorderitemmapkey}">${orderitem.lam_type?.laminatetype || ''}</td>
                    <td id="hcopy_me_${orderitem.meorderitemmapkey}">${orderitem.hardcopyquantity}</td>
                    <td id="edittype_me_${orderitem.meorderitemmapkey}">${orderitem.edit_type.edittype}</td>
                    <td>${orderitem.totalcost}</td>
                    <td>${orderitem.iscompleted === 1 ? 'Completed' : 'In Progress'}</td>
                

                    <td>
                        <div class="d-flex justify-content-start gap-1">
                            <button id="doneBtn_me${orderitem.meorderitemmapkey}"
                                type="button"
                                class="btn done-item ${doneBtnClass} text-white mark-done-btn"
                                data-id="${orderitem.meorderitemmapkey}"
                                data-orderid="${orderitem.orderkey}"
                                title="Mark as Complete"
                                ${doneBtnStyle}>
                                <i class="fas fa-check"></i>
                            </button>
                            <button id="editBtn_me${orderitem.meorderitemmapkey}" type="button" title="Edit" class="btn btn-edit edit-order" onClick="edititem_me(${orderitem.meorderitemmapkey})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button style="display:none" id="saveBtn_me${orderitem.meorderitemmapkey}"
                                type="button" class="btn btn-save save-order"title="Save"
                                onClick="saveitem_me(${orderitem.meorderitemmapkey},'${orderitem.order.order_type.ordertype}',${orderitem.order.order_type.ordertypekey},${orderitem.order_type_item.ordertypeitemkey},'${orderitem.lam_type?.lamtypekey || ''}',${orderitem.order.customerkey},${orderitem.order.isurgent},'${orderitem.order.discount}','${orderitem.order.paidcost}',${orderitem.order.studiokey},'${orderitem.order.orderid}','${orderitem.order.deliverydate}','${orderitem.order.remarks}')">
                                <i class="fas fa-save"></i>
                            </button>
                            <button id="dltBtn_me${orderitem.meorderitemmapkey}" class="btn btn-delete remove-order" data-orderid="${orderitem.orderkey}" data-id="${orderitem.meorderitemmapkey}" data-type="${orderitem.order.order_type.ordertype}"><i class="fas fa-trash"></i></button>
                        </dev>
                    </td>

                </tr>
            `;
        });

        html += '</tbody></table>';
        $('#orderitemResults_me').html(html);
    }

    function edititem_me(meorderitemmapkey) {
        // Get latest values from the table before editing
        let lamtypeElement = document.getElementById(`lamtype_me_${meorderitemmapkey}`);
        let hcopyElement = document.getElementById(`hcopy_me_${meorderitemmapkey}`);
        let edittypeElement = document.getElementById(`edittype_me_${meorderitemmapkey}`);

        // Ensure elements exist before accessing properties
        if (!hcopyElement || !edittypeElement || !lamtypeElement) {
            console.error(`Error: One or more elements missing for item ${meorderitemmapkey}`);
            return;
        }

        let lamtype = lamtypeElement.textContent.trim();
        let hcopy = hcopyElement.textContent.trim();
        let edittype = edittypeElement.textContent.trim(); // Get displayed edit type text

        // Hide Edit Button, Show Save Button
        document.getElementById(`editBtn_me${meorderitemmapkey}`).style.display = "none";
        document.getElementById(`dltBtn_me${meorderitemmapkey}`).style.display = "none";
        document.getElementById(`doneBtn_me${meorderitemmapkey}`).style.display = "none";
        document.getElementById(`saveBtn_me${meorderitemmapkey}`).style.display = "inline-block";

        // Convert Laminate Type to Input Field
        lamtypeElement.innerHTML =
            `<div class="col-md-4">
                <select style="width:150px;border-color: orange;" id="input_lamtype_me_${meorderitemmapkey}" name="lamtype" class="form-control">
                    <option value="">Select Laminate Type</option>
                    @foreach ($lamTypes as $lamType)
                        <option value="{{ $lamType->lamtypekey }}" ${lamtype === '{{ $lamType->laminatetype }}' ? 'selected' : ''}>{{ $lamType->laminatetype }}</option>
                    @endforeach
                </select>
            </div>`;

        // Convert Hard Copy to Input Field
        hcopyElement.innerHTML =
            `<div class="col-md-4">
                <input style="border-color: orange;" id="input_hcopy_me_${meorderitemmapkey}" value="${hcopy}" name="hcopy" type="text" class="form-control input-md" required="">
            </div>`;

        // Convert Edit Type to Dropdown
        edittypeElement.innerHTML =
            `<div class="col-md-4">
                <select style="width:150px;border-color: orange;" id="input_edittype_me_${meorderitemmapkey}" name="edittype" class="form-control">
                    <option value="">Select Edit Type</option>
                    @foreach ($editTypes as $editType)
                        <option value="{{ $editType->edittypekey }}" ${edittype === '{{ $editType->edittype }}' ? 'selected' : ''}>{{ $editType->edittype }}</option>
                    @endforeach
                </select>
            </div>`;
    }

    function saveitem_me(meorderitemmapkey, ordertype, ordertypekey, ordertypeitemkey, lamtypekey, customerkey, isurgent, discount, paidcost, studiokey, orderid, deliverydate, remarks) {
        dataarray=[]

        setTimeout(() => {
            let hcopy = document.querySelector(`#input_hcopy_me_${meorderitemmapkey}`)?.value || "";

            let lamtypeElement = document.querySelector(`#input_lamtype_me_${meorderitemmapkey}`);
            if (!lamtypeElement) {
                console.error("Edit type dropdown not found!");
                return;
            }
            let lamtype = lamtypeElement.options[lamtypeElement.selectedIndex].value;
            let lamtypeText = lamtypeElement.options[lamtypeElement.selectedIndex].text;

            if (!lamtype) {
                console.error("Error: laminating type is not defined or empty!");
                return; // Prevent the function from executing further if edittype is missing.
            }

            let edittypeElement = document.querySelector(`#input_edittype_me_${meorderitemmapkey}`);
            if (!edittypeElement) {
                console.error("Edit type dropdown not found!");
                return;
            }
            let edittype = edittypeElement.options[edittypeElement.selectedIndex].value;
            let edittypeText = edittypeElement.options[edittypeElement.selectedIndex].text;

            if (!edittype) {
                console.error("Error: edittype is not defined or empty!");
                return; // Prevent the function from executing further if edittype is missing.
            }

            let dataarray = {
                studiokey: studiokey,
                orderid: orderid,
                ordertypekey: ordertypekey,
                ordertype: ordertype,
                ordertypeitemkey: ordertypeitemkey,
                edittypekey: edittype,
                lamtypekey: lamtype,
                customerkey: customerkey,
                isurgent: isurgent,
                discount: discount,
                paidcost: paidcost,
                softcopycount: 0,  // Fix: Use correct variable
                hardcopycount: hcopy,   // Fix: Use correct variable
                deliverydate: deliverydate,
                remarks: remarks,
                iscompleted: 0,
                _token: "{{ csrf_token() }}" // Required for Laravel AJAX requests
            };
            console.log('Data Sent:', dataarray);

            let cells = document.querySelectorAll(`#row_me_${meorderitemmapkey} td`);
            cells.forEach(cell => {
                if (cell.cellIndex !== 0) {
                    cell.contentEditable = "false";
                    cell.classList.remove("edit-mode");
                }
            });

            $.ajax({
                    url: "{{ route('storeOrder_ss') }}",
                    type: "POST",
                    data: dataarray,
                    success: function (response) {
                        console.log("Order Updated Successfully:", response);

                        // Update the table row with latest values
                        document.getElementById(`hcopy_me_${meorderitemmapkey}`).innerHTML = hcopy;
                        document.getElementById(`edittype_me_${meorderitemmapkey}`).innerHTML = edittypeText;
                        document.getElementById(`lamtype_me_${meorderitemmapkey}`).innerHTML = lamtypeText;

                        // Show Edit Button Again
                        document.getElementById(`editBtn_me${meorderitemmapkey}`).style.display = "inline-block";
                        document.getElementById(`dltBtn_me${meorderitemmapkey}`).style.display = "inline-block";
                        document.getElementById(`doneBtn_me${meorderitemmapkey}`).style.display = "inline-block";
                        document.getElementById(`saveBtn_me${meorderitemmapkey}`).style.display = "none";
                        // render table
                        let flashbody = '<div id="flash-message" class="alert alert-success" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);z-index: 9999; padding: 15px 20px; font-size: 16px; text-align: center;background-color: #434844; color: white; border-radius: 5px; box-shadow: 0px 4px 6px rgba(0,0,0,0.1);">'
                        + response.message +'!</div>';

                        $("body").prepend(flashbody);
                            // Automatically remove the message after 2 seconds
                            setTimeout(function() {
                                $("#flash-message").fadeOut("slow", function() {
                                    $(this).remove();
                                });
                            }, 2000);
                    },
                    error: function (xhr, status, error) {
                    console.error("Error:", xhr.responseText);

                    // Attempt to parse the JSON response
                    try {
                        var response = JSON.parse(xhr.responseText);

                        // Display the error message using SweetAlert2
                        Swal.fire({
                            title: 'Failed to Create Order',
                            text: response.message || 'An unexpected error occurred.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } catch (e) {
                        // If parsing fails, display a generic error message
                        Swal.fire({
                            title: 'Failed to Create Order',
                            text: 'An unexpected error occurred.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        }, 300);
    }

    function addnew_me(orderkey){
        if (!orderkey) {
            alert("Order key is missing!");
            return;
        }
        // Get the table body
        let table = document.getElementById("itemtable_me").getElementsByTagName('tbody')[0];

        // Create a new row
        let newRow = table.insertRow();
        newRow.style.backgroundColor = "lightblue";

        // Insert cells into the row
        let itemcell = newRow.insertCell(0);
        let lamtypecell = newRow.insertCell(1);
        let hcopycell = newRow.insertCell(2);
        let edittypecell = newRow.insertCell(3);
        let costcell = newRow.insertCell(4);
        let statuscell = newRow.insertCell(5);
        let actioncell = newRow.insertCell(6);

        // Get order type key
        var otk = document.getElementById("otk")?.value || "";

        // Add content to the new cells
        itemcell.innerHTML = `
            <div style="width:150px;border-color: blue;border-width: 2px;" class="col-md-4">
                <select id="sittingitem" name="item" class="form-control">
                    <option value="">Select Item Type</option>
                </select>
            </div>
        `;

        setTimeout(() => loadOrderTypeItems(otk), 300);

        lamtypecell.innerHTML = `
            <div class="col-md-4">
                <select style="width:150px;" id="edittype_me" name="lamtype" class="form-control">
                    <option value="">Select Edit Type</option>
                    @foreach ($lamTypes as $lamType)
                        <option value="{{ $lamType->lamtypekey }}">{{ $lamType->laminatetype }}</option>
                    @endforeach
                </select>
            </div>
        `;

        hcopycell.innerHTML = `
            <div class="col-md-4">
                <input id="hcopy_me" name="hcopy" type="text" class="form-control input-md" required="">
            </div>
        `;

        edittypecell.innerHTML = `
            <div class="col-md-4">
                <select style="width:150px;" id="edittype_me" name="edittype" class="form-control">
                    <option value="">Select Edit Type</option>
                    @foreach ($editTypes as $editType)
                        <option value="{{ $editType->edittypekey }}">{{ $editType->edittype }}</option>
                    @endforeach
                </select>
            </div>
        `;

        costcell.innerHTML = '';
        statuscell.innerHTML = '';

        actioncell.innerHTML = `
            <button id="addBtn_me" type="button" class="btn btn-primary" onClick="additem_me(this)" data-orderkey="${orderkey}">
                Add
            </button>
        `;
        document.getElementById("addBtn_me").style.display = "inline-block";
    }

    function additem_me(btn) {
        let orderkey = btn.getAttribute("data-orderkey");
        // Get the row (parent of the button)
        let row = btn.closest("tr");

        // Extract input values
        let hcopy = row.querySelector("input[name='hcopy']").value;
        let edittype = row.querySelector("select[name='edittype']").value;
        let lamtype = row.querySelector("select[name='lamtype']").value;
        let item = row.querySelector("select[name='item']").value;

        $.ajax({
            url: "/order-itemsummary/" + orderkey + "?ordertype=" + encodeURIComponent(orderType),
            type: "GET",
            data: {
                orderkey: orderkey,
                hcopy: hcopy,
                edittype: edittype,
                lamtype: lamtype,
                item: item,
                _token: "{{ csrf_token() }}" // CSRF Token for security
            },
            success: function (response) {
                if (!response || response.length === 0) {
                    console.log("No order items returned from server.");
                } else {
                    console.log('data call working ...............')
                    createitem_me(response,{ orderkey, hcopy, edittype, lamtype, item});
                }
            }
        });
    }

    function createitem_me(orderdata,formData) {
        if (!orderdata.orderItems.length) {
            $('#orderitemResults').html(
                // '<div class="alert alert-info">No Order Items found.</div>'
            );
            return;
        }

        let orderdetail=orderdata.orderItems[0];

        //dataarray=[]

        let dataarray = {
            studiokey: orderdetail.order.studiokey,
            orderid: orderdetail.order.orderid,
            ordertypekey: orderdetail.order.ordertypekey,
            ordertype: orderdetail.order.order_type.ordertype,
            ordertypeitemkey: formData.item,
            edittypekey: formData.edittype,
            lamtypekey: formData.lamtype,
            customerkey: orderdetail.order.customerkey,
            isurgent: orderdetail.order.isurgent,
            discount: orderdetail.order.discount,
            paidcost: orderdetail.order.paidcost,
            softcopycount: 0,  // Fix: Use correct variable
            hardcopycount: formData.hcopy,   // Fix: Use correct variable
            deliverydate: orderdetail.order.deliverydate,
            remarks: orderdetail.order.remarks,
            iscompleted: 0,
            _token: "{{ csrf_token() }}" // Required for Laravel AJAX requests
        };

        $.ajax({
                url: "{{ route('storeOrder_ss') }}",
                type: "POST",
                data: dataarray,
                success: function (response) {
                    console.log("Order Item Created Successfully:", response);

                    setTimeout(() => {
                        loaditemdata_ME(formData.orderkey);
                        $('orderviewmodal_ME').modal('show');
                    }, 500);

                    document.getElementById("addBtn_me").style.display = "none";
                    // render table
                    let flashbody = '<div id="flash-message" class="alert alert-success" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);z-index: 9999; padding: 15px 20px; font-size: 16px; text-align: center;background-color: #434844; color: white; border-radius: 5px; box-shadow: 0px 4px 6px rgba(0,0,0,0.1);">'
                    + response.message +'!</div>';

                    $("body").prepend(flashbody);
                        // Automatically remove the message after 2 seconds
                        setTimeout(function() {
                            $("#flash-message").fadeOut("slow", function() {
                                $(this).remove();
                            });
                        }, 2000);
                },
                error: function (xhr, status, error) {
                console.error("Error:", xhr.responseText);

                // Attempt to parse the JSON response
                try {
                    var response = JSON.parse(xhr.responseText);

                    // Display the error message using SweetAlert2
                    Swal.fire({
                        title: 'Failed to Create Order',
                        text: response.message || 'An unexpected error occurred.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                } catch (e) {
                    // If parsing fails, display a generic error message
                    Swal.fire({
                        title: 'Failed to Create Order',
                        text: 'An unexpected error occurred.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            }
        });

    }

    // ******************* End Media Related *************************

    // ******************* Frames Retated ****************************

    function loaditemdata_FR(okey,orderType){
        let orderkey = okey;
        $.ajax({
            url: "/order-itemsummary/" + orderkey + "?ordertype=" + encodeURIComponent(orderType),
            type: "GET",
            data: {
                orderkey: orderkey,
                _token: "{{ csrf_token() }}" // CSRF Token for security
            },
            success: function (response) {
                console.log('Server response_FR:', response); // Debugging

                if (!response || response.length === 0) {
                    console.log("No order items returned from server.");
                } else {
                    displayOrderitemSearchResults_FR(response,orderkey,orderType);
                }
            }
        });

    }

    function displayOrderitemSearchResults_FR(orderitems,orderkey,orderType) {
        if (!orderitems.orderItems.length) {
            $('#orderitemResults_fr').html(
                // '<div class="alert alert-info">No Order Items found.</div>'
            );
            return;
        }

    //show order summary
        // $.ajax({
        //     url: "/order-itemsummary/" + orderkey + "?ordertype=" + encodeURIComponent(orderType),
        //     type: "GET",
        //     success: function (response) {
        //         if (response.status === "success") {
        //             let orderSummaryHtml = "";
        //             let orderSummaryTotalHtml = "";
        //             let orderTotalCost = 0;
        //             let orderDiscount = 0;
        //             let discountAmount = 0;
        //             let paidAmount = 0;

        //             response.orderItems.forEach(item => {
        //                 orderTotalCost += parseFloat(item.totalcost) || 0;
        //                 orderDiscount = parseFloat(item.order.discount) || 0;
        //                 paidAmount = parseFloat(item.order.paidcost) || 0;
        //             });

        //             discountAmount = (orderTotalCost * orderDiscount) / 100;
        //             let balanceDue = (orderTotalCost - discountAmount) - paidAmount;

        //             orderSummaryTotalHtml = `
        //             </br><table>
        //                 <tr>
        //                     <th style="width: 50%;">Total Cost</th>
        //                     <td><span id="total-cost">Rs ${orderTotalCost.toFixed(2)}</span></td>
        //                 </tr>
        //                 <tr>
        //                     <th>Discount (${orderDiscount}%)</th>
        //                     <td><span id="total-cost">Rs ${discountAmount.toFixed(2)}</span></td>
        //                 </tr>
        //                 <tr>
        //                     <th>Paid Amount</th>
        //                     <td><span id="total-cost">Rs ${paidAmount.toFixed(2)}</span></td>
        //                 </tr>
        //                 <tr>
        //                     <th>Balance Due</th>
        //                     <td><span id="balance-due" style="font-weight:700;">Rs ${balanceDue.toFixed(2)}</span></td>
        //                 </tr>
        //             </table> `;

        //             $("#ordersummary_FR").html(orderSummaryTotalHtml);
        //         }
        //     },
        //     error: function (xhr) {
        //         console.error("Error fetching order summary:", xhr);
        //     }
        // });

        // end order summary
        let html = `
            <div style="margin-bottom: 10px;">
                <h5 style="margin-bottom:10px;">Order Type: <strong>${orderType}</strong></h5>
                <button id="addnew" onClick="addnew_fr('${orderkey}')" type="button" class="btn btn-info" style="font-size:15px;">+ Add New Item</button>
            </div>
            <table id="itemtable_fr" class="table table-bordered">
                <thead>
                    <tr id="row_0">
                        <th>Type</th>
                        <th>Size</th>
                        <th>F# Size</th>
                        <th>Frame Type</th>
                        <th>Quantity</th>
                        <th>Cost (LKR)</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
        `;

        console.log('Order detailsaaa',orderitems);

        orderitems.orderItems.forEach(function(orderitem) {
            const isCompleted = orderitem.iscompleted === 1;
            const doneBtnClass = isCompleted ? 'btn-done' : 'btn-secondary';
            const doneBtnStyle = isCompleted ? '' : 'style="background-color: gray;"';

            html += `
                <tr id="row_fr_${orderitem.frorderitemmapkey}">
                    <td id="frametype_fr_${orderitem.frorderitemmapkey}">${orderitem.frame_type.frametype}</td>
                    <td id="framesize_fr_${orderitem.frorderitemmapkey}">${orderitem.frame_size.size}</td>
                    <td id="sframesize_fr_${orderitem.frorderitemmapkey}">${orderitem.subframe_size?.framesize || ''}</td>
                    <td id="sframetype_fr_${orderitem.frorderitemmapkey}">${orderitem.subframe_type?.subframetype || ''}</td>
                    <td id="quantity_fr_${orderitem.frorderitemmapkey}">${orderitem.quantity}</td>
                    <td>${orderitem.totalcost}</td>
                    <td>${orderitem.iscompleted === 1 ? 'Completed' : 'In Progress'}</td>
                    <td> 
                        <div class="d-flex justify-content-start gap-1">
                            <button id="doneBtn_fr_${orderitem.frorderitemmapkey}"
                                type="button"
                                class="btn done-item ${doneBtnClass} text-white mark-done-btn"
                                data-id="${orderitem.frorderitemmapkey}"
                                data-orderid="${orderitem.orderkey}"
                                title="Mark as Complete"
                                ${doneBtnStyle}>
                                <i class="fas fa-check"></i>
                            </button>
                            <button id="editBtn_fr_${orderitem.frorderitemmapkey}" type="button" class="btn btn-edit edit-order" onClick="edititem_fr(${orderitem.frorderitemmapkey},'${orderitem.frame_type.frametype}','${orderitem.frame_size.size}','${orderitem.subframe_size?.framesize || ''}','${orderitem.subframe_type?.subframetype || ''}','${orderitem.quantity}')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button style="display:none" id="saveBtn_fr_${orderitem.frorderitemmapkey}"
                                type="button" class="btn btn-save save-order"title="Save"
                                onClick="saveitem_fr(${orderitem.frorderitemmapkey},'${orderitem.order.order_type?.ordertype || ''}',${orderitem.order.order_type?.ordertypekey || ''},'${orderitem.order_type_item?.ordertypeitemkey || ''}','${orderitem.lam_type?.lamtypekey || ''}','${orderitem.order?.customerkey || ''}',${orderitem.order?.isurgent},'${orderitem.order?.discount || ''}','${orderitem.order?.paidcost || ''}',${orderitem.order?.studiokey || ''},'${orderitem.order?.orderid || ''}','${orderitem.order?.deliverydate || ''}','${orderitem.order?.remarks || ''}','${orderitem.frame_type.frametype}','${orderitem.frame_type.frametypekey}','${orderitem.quantity}')">
                                <i class="fas fa-save"></i>
                            </button>
                            <button id="dltBtn_fr_${orderitem.frorderitemmapkey}" class="btn btn-delete remove-order" data-orderid="${orderitem.orderkey}" data-id="${orderitem.frorderitemmapkey}" data-type="${orderitem.order.order_type.ordertype}"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `;
        });

        html += '</tbody></table>';
        $('#orderitemResults_fr').html(html);
    }

    function edititem_fr(frorderitemmapkey) {
        // Get latest values from the table before editing
        let frtypeElement = document.getElementById(`frametype_fr_${frorderitemmapkey}`);
        let frsizeElement = document.getElementById(`framesize_fr_${frorderitemmapkey}`);
        let sfrsizeElement = document.getElementById(`sframesize_fr_${frorderitemmapkey}`);
        let sfrtypeElement = document.getElementById(`sframetype_fr_${frorderitemmapkey}`);
        let quantityElement = document.getElementById(`quantity_fr_${frorderitemmapkey}`);

        if (frtypeElement.textContent==='Fiber Frame'){
            // Ensure elements exist before accessing properties
            if (!frsizeElement || !sfrsizeElement || !sfrtypeElement) {
                console.error(`Error: One or more elements missing for item ${frorderitemmapkey}`);
                return;
            }

            let frsize = frsizeElement.textContent.trim();
            let sfrsize = sfrsizeElement.textContent.trim();
            let sfrtype = sfrtypeElement.textContent.trim();
            let quantity = quantityElement.textContent.trim();

            // Convert Frame Size to Dropdown
            frsizeElement.innerHTML =
                `<div class="col-md-4">
                    <select style="width:150px;border-color: orange;" id="input_frsize_fr_${frorderitemmapkey}" name="frsize" class="form-control">
                        <option value="">Select Frame Size</option>
                        @foreach ($frameSizes as $frameSize)
                            <option value="{{ $frameSize->framesizekey }}" ${frsize === '{{ $frameSize->size }}' ? 'selected' : ''}>{{ $frameSize->size }}</option>
                        @endforeach
                    </select>
                </div>`;

            // Convert Sub Frame Size to Dropdown
            sfrsizeElement.innerHTML =
                `<div class="col-md-4">
                    <select style="width:150px;border-color: orange;" id="input_sfrsize_fr_${frorderitemmapkey}" name="sfrsize" class="form-control">
                        <option value="">Select F# Size</option>
                        @foreach ($frameSubSizes as $frameSubSize)
                            <option value="{{ $frameSubSize->subframesizekey }}" ${sfrsize === '{{ $frameSubSize->framesize }}' ? 'selected' : ''}>{{ $frameSubSize->framesize }}</option>
                        @endforeach
                    </select>
                </div>`;

             // Convert Sub Frame Type to Dropdown
             sfrtypeElement.innerHTML =
                `<div class="col-md-4">
                    <select style="width:150px;border-color: orange;" id="input_sfrtype_fr_${frorderitemmapkey}" name="sfrtype" class="form-control">
                        <option value="">Select Frame Type</option>
                        @foreach ($frameSubTypes as $frameSubType)
                            <option value="{{ $frameSubType->subframetypekey }}" ${sfrtype === '{{ $frameSubType->subframetype }}' ? 'selected' : ''}>{{ $frameSubType->subframetype }}</option>
                        @endforeach
                    </select>
                </div>`;

            quantityElement.innerHTML =
                `<div class="col-md-4">
                    <input style="border-color: orange;" id="input_quantity_fr_${frorderitemmapkey}" value="${quantity}" name="quantity" type="text" class="form-control input-md" required="">
                </div>`;
        }
        else{
            // Ensure elements exist before accessing properties
            if (!frsizeElement) {
                console.error(`Error: One or more elements missing for item ${frorderitemmapkey}`);
                return;
            }

            let frsize = frsizeElement.textContent.trim();
            let quantity = quantityElement.textContent.trim();

            // Convert Frame Size to Dropdown
            frsizeElement.innerHTML =
                `<div class="col-md-4">
                    <select style="width:150px;border-color: orange;" id="input_frsize_fr_${frorderitemmapkey}" name="frsize" class="form-control">
                        <option value="">Select Frame Size</option>
                        @foreach ($frameSizes as $frameSize)
                            <option value="{{ $frameSize->framesizekey }}" ${frsize === '{{ $frameSize->size }}' ? 'selected' : ''}>{{ $frameSize->size }}</option>
                        @endforeach
                    </select>
                </div>`;

            quantityElement.innerHTML =
                `<div class="col-md-4">
                    <input style="border-color: orange;" id="input_quantity_fr_${frorderitemmapkey}" value="${quantity}" name="quantity" type="text" class="form-control input-md" required="">
                </div>`;
        }



        // Hide Edit Button, Show Save Button
        document.getElementById(`editBtn_fr_${frorderitemmapkey}`).style.display = "none";
        document.getElementById(`dltBtn_fr_${frorderitemmapkey}`).style.display = "none";
        document.getElementById(`doneBtn_fr_${frorderitemmapkey}`).style.display = "none";
        document.getElementById(`saveBtn_fr_${frorderitemmapkey}`).style.display = "inline-block";


    }

    function saveitem_fr(frorderitemmapkey, ordertype, ordertypekey, ordertypeitemkey, lamtypekey, customerkey, isurgent, discount, paidcost, studiokey, orderid, deliverydate, remarks, frametype, frametypekey, quantity) {
        let dataarray = [];
        let framesizeText = '';
        let sframesizeText = '';
        let sframetypeText = '';

        setTimeout(() => {
            if (frametype === 'Fiber Frame') {

                quantity = document.querySelector(`#input_quantity_fr_${frorderitemmapkey}`)?.value || "";

                // Read Frame Size Element
                let framesizeElement = document.querySelector(`#input_frsize_fr_${frorderitemmapkey}`);
                if (!framesizeElement) {
                    console.error("Frame size dropdown not found!");
                    return;
                }
                let framesize = framesizeElement.options[framesizeElement.selectedIndex].value;
                framesizeText = framesizeElement.options[framesizeElement.selectedIndex].text;

                if (!framesize) {
                    console.error("Error: frame size is not defined or empty!");
                    return;
                }

                // Read Sub Frame Size Element
                let sframesizeElement = document.querySelector(`#input_sfrsize_fr_${frorderitemmapkey}`);
                if (!sframesizeElement) {
                    console.error("F# Size dropdown not found!");
                    return;
                }
                let sframesize = sframesizeElement.options[sframesizeElement.selectedIndex].value;
                sframesizeText = sframesizeElement.options[sframesizeElement.selectedIndex].text;

                if (!sframesize) {
                    console.error("Error: F# size not defined or empty!");
                    return;
                }

                // Read Sub Frame Type Element
                let sframetypeElement = document.querySelector(`#input_sfrtype_fr_${frorderitemmapkey}`);
                if (!sframetypeElement) {
                    console.error("F# Type dropdown not found!");
                    return;
                }
                let sframetype = sframetypeElement.options[sframetypeElement.selectedIndex].value;
                sframetypeText = sframetypeElement.options[sframetypeElement.selectedIndex].text;

                if (!sframetype) {
                    console.error("Error: Frame Type not defined or empty!");
                    return;
                }

                dataarray = {
                    studiokey: studiokey,
                    orderid: orderid,
                    ordertypekey: ordertypekey,
                    frorderitemmapkey: frorderitemmapkey,
                    ordertype: ordertype,
                    customerkey: customerkey,
                    isurgent: isurgent,
                    discount: discount,
                    paidcost: paidcost,
                    deliverydate: deliverydate,
                    remarks: remarks,
                    iscompleted: 0,
                    frametypekey: frametypekey,
                    framesizekey: framesize,
                    subframesizekey: sframesize,
                    subframetypekey: sframetype,
                    quantity: quantity,
                    _token: "{{ csrf_token() }}"
                };
            } else {

                quantity = document.querySelector(`#input_quantity_fr_${frorderitemmapkey}`)?.value || "";

                let framesizeElement = document.querySelector(`#input_frsize_fr_${frorderitemmapkey}`);
                if (!framesizeElement) {
                    console.error("Frame size dropdown not found!");
                    return;
                }
                let framesize = framesizeElement.options[framesizeElement.selectedIndex].value;
                framesizeText = framesizeElement.options[framesizeElement.selectedIndex].text;

                if (!framesize) {
                    console.error("Error: frame size is not defined or empty!");
                    return;
                }

                dataarray = {
                    studiokey: studiokey,
                    orderid: orderid,
                    ordertypekey: ordertypekey,
                    frorderitemmapkey: frorderitemmapkey,
                    ordertype: ordertype,
                    customerkey: customerkey,
                    isurgent: isurgent,
                    discount: discount,
                    paidcost: paidcost,
                    deliverydate: deliverydate,
                    remarks: remarks,
                    iscompleted: 0,
                    frametypekey: frametypekey,
                    framesizekey: framesize,
                    subframesizekey: '',
                    subframetypekey: '',
                    quantity: quantity,
                    _token: "{{ csrf_token() }}"
                };
            }

            console.log('Data Sent:', dataarray);

            // Make table cells read-only again
            let cells = document.querySelectorAll(`#row_fr_${frorderitemmapkey} td`);
            cells.forEach(cell => {
                if (cell.cellIndex !== 0) {
                    cell.contentEditable = "false";
                    cell.classList.remove("edit-mode");
                }
            });

            // Send data to server
            $.ajax({
                url: "{{ route('storeOrder_fr') }}",
                type: "POST",
                data: dataarray,
                success: function (response) {
                    console.log("Order Updated Successfully:", response);

                    document.getElementById(`framesize_fr_${frorderitemmapkey}`).innerHTML = framesizeText;
                    document.getElementById(`sframesize_fr_${frorderitemmapkey}`).innerHTML = sframesizeText;
                    document.getElementById(`sframetype_fr_${frorderitemmapkey}`).innerHTML = sframetypeText;
                    document.getElementById(`quantity_fr_${frorderitemmapkey}`).innerHTML = quantity;

                    document.getElementById(`editBtn_fr_${frorderitemmapkey}`).style.display = "inline-block";
                    document.getElementById(`dltBtn_fr_${frorderitemmapkey}`).style.display = "inline-block";
                    document.getElementById(`doneBtn_fr_${frorderitemmapkey}`).style.display = "inline-block";
                    document.getElementById(`saveBtn_fr_${frorderitemmapkey}`).style.display = "none";

                    // Flash message
                    let flashbody = '<div id="flash-message" class="alert alert-success" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);z-index: 9999; padding: 15px 20px; font-size: 16px; text-align: center;background-color: #434844; color: white; border-radius: 5px; box-shadow: 0px 4px 6px rgba(0,0,0,0.1);">'
                        + response.message + '!</div>';

                    $("body").prepend(flashbody);
                    setTimeout(function () {
                        $("#flash-message").fadeOut("slow", function () {
                            $(this).remove();
                        });
                    }, 2000);
                },
                error: function (xhr, status, error) {
                    console.error("Error:", xhr.responseText);

                    try {
                        var response = JSON.parse(xhr.responseText);
                        Swal.fire({
                            title: 'Failed to Create Order',
                            text: response.message || 'An unexpected error occurred.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } catch (e) {
                        Swal.fire({
                            title: 'Failed to Create Order',
                            text: 'An unexpected error occurred.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        }, 300);
    }

    function addnew_fr(orderkey) {
        if (!orderkey) {
            alert("Order key is missing!");
            return;
        }

        // Get order type key
        var otk = document.getElementById("otk")?.value || "";

        // Get the table body
        let table = document.getElementById("itemtable_fr").getElementsByTagName('tbody')[0];

        // Create a new row
        let newRow = table.insertRow();
        newRow.style.backgroundColor = "lightblue";

        // Insert cells into the row
        let typecell = newRow.insertCell(0);
        let sizecell = newRow.insertCell(1);
        let sfsizecell = newRow.insertCell(2);
        let sftypecell = newRow.insertCell(3);
        let quantitycell = newRow.insertCell(4);
        let costcell = newRow.insertCell(5);
        let statuscell = newRow.insertCell(6);
        let actioncell = newRow.insertCell(7);

        setTimeout(() => loadOrderTypeItems(otk), 300);

        // Create unique IDs for the new row elements
        let rowId = Date.now(); // Simple way to make unique IDs per row

        // Frame Type Cell
        typecell.innerHTML = `
            <div class="col-md-4">
                <select style="width:150px;" id="ftype_fr_${rowId}" name="frametype" class="form-control"
                    onchange="toggleSubFrameFields_fr('${rowId}')">
                    <option value="">Select Type</option>
                    @foreach ($frameTypes as $frameType)
                        <option value="{{ $frameType->frametypekey }}">{{ $frameType->frametype }}</option>
                    @endforeach
                </select>
            </div>
        `;

        // Frame Size Cell
        sizecell.innerHTML = `
            <div class="col-md-4">
                <select style="width:150px;" id="fsize_fr_${rowId}" name="framesize" class="form-control">
                    <option value="">Select Size</option>
                    @foreach ($frameSizes as $frameSize)
                        <option value="{{ $frameSize->framesizekey }}">{{ $frameSize->size }}</option>
                    @endforeach
                </select>
            </div>
        `;

        // Sub Frame Size Cell
        sfsizecell.innerHTML = `
            <div class="col-md-4">
                <select style="width:150px;" id="sfsize_fr_${rowId}" name="sframesize" class="form-control" disabled>
                    <option value="">Select F# Size</option>
                    @foreach ($frameSubSizes as $frameSubSize)
                        <option value="{{ $frameSubSize->subframesizekey }}">{{ $frameSubSize->framesize }}</option>
                    @endforeach
                </select>
            </div>
        `;

        // Sub Frame Type Cell
        sftypecell.innerHTML = `
            <div class="col-md-4">
                <select style="width:150px;" id="sftype_fr_${rowId}" name="sframetype" class="form-control" disabled>
                    <option value="">Select Frame Type</option>
                    @foreach ($frameSubTypes as $frameSubType)
                        <option value="{{ $frameSubType->subframetypekey }}">{{ $frameSubType->subframetype }}</option>
                    @endforeach
                </select>
            </div>
        `;

        quantitycell.innerHTML = `
            <div class="col-md-4">
                <input id="quantity_fr" name="quantity" type="text" class="form-control input-md" required="" style="width:150px;">
            </div>
        `;

        costcell.innerHTML = '';
        statuscell.innerHTML = '';

        actioncell.innerHTML = `
            <button id="addBtn_fr_${rowId}" type="button" class="btn btn-primary" onClick="additem_fr(this)" data-orderkey="${orderkey}">
                Add
            </button>
        `;
    }

    function toggleSubFrameFields_fr(rowId) {
        const frameTypeSelect = document.getElementById(`ftype_fr_${rowId}`);
        const selectedText = frameTypeSelect.options[frameTypeSelect.selectedIndex].text;

        const sfSizeSelect = document.getElementById(`sfsize_fr_${rowId}`);
        const sfTypeSelect = document.getElementById(`sftype_fr_${rowId}`);

        if (selectedText === "Fiber Frame") {
            sfSizeSelect.disabled = false;
            sfTypeSelect.disabled = false;
        } else {
            sfSizeSelect.disabled = true;
            sfSizeSelect.selectedIndex = 0;

            sfTypeSelect.disabled = true;
            sfTypeSelect.selectedIndex = 0;
        }
    }

    function additem_fr(btn) {
        let orderkey = btn.getAttribute("data-orderkey");
        // Get the row (parent of the button)
        let row = btn.closest("tr");

        // Extract input values
        let frametype = row.querySelector("select[name='frametype']").value;
        let framesize = row.querySelector("select[name='framesize']").value;
        let sframesize = row.querySelector("select[name='sframesize']").value;
        let sframetype = row.querySelector("select[name='sframetype']").value;
        let quantity = row.querySelector("input[name='quantity']").value;

        $.ajax({
            url: "/order-itemsummary/" + orderkey + "?ordertype=" + encodeURIComponent(orderType),
            type: "GET",
            data: {
                orderkey: orderkey,
                _token: "{{ csrf_token() }}" // CSRF Token for security
            },
            success: function (response) {
                if (!response || response.length === 0) {
                    console.log("No order items returned from server.");
                } else {
                    console.log('data call working ...............')
                    createitem_fr(response,{ orderkey, frametype, framesize, sframesize, sframetype, quantity});
                }
            }
        });
    }

    function createitem_fr(orderdata,formData) {
        if (!orderdata.orderItems.length) {
            $('#orderitemResults').html(
                // '<div class="alert alert-info">No Order Items found.</div>'
            );
            return;
        }

        let orderdetail=orderdata.orderItems[0];

        let dataarray = {
            studiokey: orderdetail.order.studiokey,
            orderid: orderdetail.order.orderid,
            ordertypekey: orderdetail.order.ordertypekey,
            ordertype: orderdetail.order.order_type.ordertype,
            customerkey: orderdetail.order.customerkey,
            isurgent: orderdetail.order.isurgent,
            discount: orderdetail.order.discount,
            paidcost: orderdetail.order.paidcost,
            deliverydate: orderdetail.order.deliverydate,
            remarks: orderdetail.order.remarks,
            iscompleted:0,
            frametypekey : formData.frametype,
            framesizekey : formData.framesize,
            subframesizekey : formData.sframesize,
            subframetypekey : formData.sframetype,
            quantity:formData.quantity,
            _token: "{{ csrf_token() }}"
        };

        $.ajax({
                url: "{{ route('storeOrder_fr') }}",
                type: "POST",
                data: dataarray,
                success: function (response) {
                    console.log("Order Item Created Successfully:", response);

                    setTimeout(() => {
                        loaditemdata_FR(formData.orderkey);
                        $('orderviewmodal_FR').modal('show');
                    }, 500);

                    let addBtn = document.getElementById("addBtn_fr");
                    if (addBtn) {
                        addBtn.style.display = "none";
                    }
                    // render table
                    let flashbody = '<div id="flash-message" class="alert alert-success" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);z-index: 9999; padding: 15px 20px; font-size: 16px; text-align: center;background-color: #434844; color: white; border-radius: 5px; box-shadow: 0px 4px 6px rgba(0,0,0,0.1);">'
                    + response.message +'!</div>';

                    $("body").prepend(flashbody);
                        // Automatically remove the message after 2 seconds
                        setTimeout(function() {
                            $("#flash-message").fadeOut("slow", function() {
                                $(this).remove();
                            });
                        }, 2000);
                },
                error: function (xhr, status, error) {
                console.error("Error:", xhr.responseText);

                // Attempt to parse the JSON response
                try {
                    var response = JSON.parse(xhr.responseText);

                    // Display the error message using SweetAlert2
                    Swal.fire({
                        title: 'Failed to Create Order',
                        text: response.message || 'An unexpected error occurred.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                } catch (e) {
                    // If parsing fails, display a generic error message
                    Swal.fire({
                        title: 'Failed to Create Order',
                        text: 'An unexpected error occurred.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            }
        });

    }



     // ******************* End Frames Related *************************

    // ******************* Extra Copy Retated ************************

    function loaditemdata_EC(okey,orderType){
        let orderkey = okey;
        $.ajax({
            url: "/order-itemsummary/" + orderkey + "?ordertype=" + encodeURIComponent(orderType),
            type: "GET",
            data: {
                orderkey: orderkey,
                _token: "{{ csrf_token() }}" // CSRF Token for security
            },
            success: function (response) {
                    console.log('Itemsearch result EC',response)
                    displayOrderitemSearchResults_EC(response,orderkey,orderType);

            }
        });

    }

    let globalOrderItems_EC = [];

    function displayOrderitemSearchResults_EC(orderitems,orderkey,orderType) {


        if (!orderitems.orderItems.length) {
            $('#orderitemResults_EC').html(
                // '<div class="alert alert-info">No Order Items found.</div>'
            );
            return;
        }

        globalOrderItems_EC = orderitems.orderItems;

        let html = `
            <div style="margin-bottom: 10px;">
                <h5 style="margin-bottom:10px;">Order Type: <strong>${orderType}</strong></h5>
            </div>
            <table id="itemtable_EC" class="table table-bordered">
            <thead>
                <tr id="row_0">
                    <th>Item Type</th>
                    <th>Original Order</th>
                    <th>Hard Copied</th>
                    <th>Edit Type</th>
                    <th>Cost (LKR)</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
        `;

        orderitems.orderItems.forEach(function(orderitem) {
            const isCompleted = orderitem.iscompleted === 1;
            const doneBtnClass = isCompleted ? 'btn-done' : 'btn-secondary';
            const doneBtnStyle = isCompleted ? '' : 'style="background-color: gray;"';

            html += `
                <tr id="row_ec_${orderitem.ecorderitemmapkey}" data-id="${orderitem.ecorderitemmapkey}">
                    <td id="name_ec_${orderitem.ecorderitemmapkey}">${orderitem.order_type_item.itemname}</td>
                    <td id="orionum_ec_${orderitem.ecorderitemmapkey}">${orderitem.original_order.orderid}</td>
                    <td id="hcopy_ec_${orderitem.ecorderitemmapkey}">${orderitem.hardcopyquantity}</td>
                    <td id="edittype_ec_${orderitem.ecorderitemmapkey}">${orderitem.edit_type.edittype}</td>
                    <td>${orderitem.totalcost}</td>
                    <td>${orderitem.iscompleted === 1 ? 'Completed' : 'In Progress'}</td>


                    <td>
                        <div class="d-flex justify-content-start gap-1">
                            <button id="doneBtn_ec_${orderitem.ecorderitemmapkey}"
                                type="button"
                                class="btn done-item ${doneBtnClass} text-white mark-done-btn"
                                data-id="${orderitem.ecorderitemmapkey}"
                                data-orderid="${orderitem.orderkey}"
                                title="Mark as Complete"
                                ${doneBtnStyle}>
                                <i class="fas fa-check"></i>
                            </button>

                            <button id="editBtn_ec_${orderitem.ecorderitemmapkey}" type="button" title="Edit" class="btn btn-edit edit-order" onClick="edititem_ec(${orderitem.ecorderitemmapkey})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button style="display:none" id="saveBtn_ec_${orderitem.ecorderitemmapkey}"
                                type="button" class="btn btn-save save-order"title="Save"
                                onClick="saveitem_ec(${orderitem.ecorderitemmapkey},'${orderitem.order.order_type.ordertype}',${orderitem.order.order_type.ordertypekey},${orderitem.order_type_item.ordertypeitemkey},'${orderitem.lam_type?.lamtypekey || ''}',${orderitem.order.customerkey},${orderitem.order.isurgent},'${orderitem.order.discount}','${orderitem.order.paidcost}',${orderitem.order.studiokey},'${orderitem.order.orderid}','${orderitem.order.deliverydate}','${orderitem.order.remarks}',${orderitem.hardcopyquantity},'${orderitem.edit_type.edittype}','${orderitem.original_order.orderid}','${orderitem.original_order.orderkey}')">
                                <i class="fas fa-save"></i>
                            </button>
                            <button id="dltBtn_ec_${orderitem.ecorderitemmapkey}" class="btn btn-delete remove-order" data-orderid="${orderitem.orderkey}" data-id="${orderitem.ecorderitemmapkey}" data-type="${orderitem.order.order_type.ordertype}"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>

                </tr>
            `;
        });
        html += '</tbody></table>';
        $('#orderitemResults_EC').html(html);
        //show order summary
        $.ajax({
            url: "/order-itemsummary/" + orderkey + "?ordertype=" + encodeURIComponent(orderType),
            type: "GET",
            success: function (response) {
                if (response.status === "success") {
                    let orderSummaryHtml = "";
                    let orderSummaryTotalHtml = "";
                    let orderTotalCost = 0;
                    let orderDiscount = 0;
                    let discountAmount = 0;
                    let paidAmount = 0;

                    response.orderItems.forEach(item => {
                        orderTotalCost += parseFloat(item.totalcost) || 0;
                        orderDiscount = parseFloat(item.order.discount) || 0;
                        paidAmount = parseFloat(item.order.paidcost) || 0;
                    });

                    discountAmount = (orderTotalCost * orderDiscount) / 100;
                    let balanceDue = (orderTotalCost - discountAmount) - paidAmount;

                    orderSummaryTotalHtml = `
                    </br><table>
                        <tr>
                            <th style="width: 50%;">Total Cost</th>
                            <td><span id="total-cost">Rs ${orderTotalCost.toFixed(2)}</span></td>
                        </tr>
                        <tr>
                            <th>Discount (${orderDiscount}%)</th>
                            <td><span id="total-cost">Rs ${discountAmount.toFixed(2)}</span></td>
                        </tr>
                        <tr>
                            <th>Paid Amount</th>
                            <td><span id="total-cost">Rs ${paidAmount.toFixed(2)}</span></td>
                        </tr>
                        <tr>
                            <th>Balance Due</th>
                            <td><span id="balance-due" style="font-weight:700;">Rs ${balanceDue.toFixed(2)}</span></td>
                        </tr>
                    </table> `;

                    $("#ordersummary_EC").html(orderSummaryTotalHtml);
                }
            },
            error: function (xhr) {
                console.error("Error fetching order summary:", xhr);
            }
        });


    }

    function edititem_ec(ecorderitemmapkey) {
        // Get latest values from the table before editing
        let hcopyElement = document.getElementById(`hcopy_ec_${ecorderitemmapkey}`);
        let edittypeElement = document.getElementById(`edittype_ec_${ecorderitemmapkey}`);

        // Ensure elements exist before accessing properties
        if (!hcopyElement || !edittypeElement) {
            console.error(`Error: One or more elements missing for item ${ecorderitemmapkey}`);
            return;
        }
        let hcopy = hcopyElement.textContent.trim();
        let edittype = edittypeElement.textContent.trim(); // Get displayed edit type text

        // Hide Edit Button, Show Save Button
        document.getElementById(`editBtn_ec_${ecorderitemmapkey}`).style.display = "none";
        document.getElementById(`dltBtn_ec_${ecorderitemmapkey}`).style.display = "none";
        document.getElementById(`doneBtn_ec_${ecorderitemmapkey}`).style.display = "none";
        document.getElementById(`saveBtn_ec_${ecorderitemmapkey}`).style.display = "inline-block";

        // Convert Hard Copy to Input Field
        hcopyElement.innerHTML =
            `<div class="col-md-4">
                <input style="border-color: orange;" id="input_hcopy_ec_${ecorderitemmapkey}" value="${hcopy}" name="hcopy" type="text" class="form-control input-md" required="">
            </div>`;

        // Convert Edit Type to Dropdown
        edittypeElement.innerHTML =
            `<div class="col-md-4">
                <select style="width:150px;border-color: orange;" id="input_edittype_ec_${ecorderitemmapkey}" name="edittype" class="form-control">
                    <option value="">Select Edit Type</option>
                    @foreach ($editTypes as $editType)
                        <option value="{{ $editType->edittypekey }}" ${edittype === '{{ $editType->edittype }}' ? 'selected' : ''}>{{ $editType->edittype }}</option>
                    @endforeach
                </select>
            </div>`;
    }

    function saveitem_ec(ecorderitemmapkey, ordertype, ordertypekey, ordertypeitemkey, lamtypekey, customerkey, isurgent, discount, paidcost, studiokey, orderid, deliverydate, remarks, hcopy, edittype, originalorderid, originalorderkey) {
        dataarray=[]
        setTimeout(() => {
            let hcopy = document.querySelector(`#input_hcopy_ec_${ecorderitemmapkey}`)?.value || "";

            let edittypeElement = document.querySelector(`#input_edittype_ec_${ecorderitemmapkey}`);
            if (!edittypeElement) {
                console.error("Edit type dropdown not found!");
                return;
            }
            let edittype = edittypeElement.options[edittypeElement.selectedIndex].value;
            let edittypeText = edittypeElement.options[edittypeElement.selectedIndex].text;

            if (!edittype) {
                console.error("Error: edittype is not defined or empty!");
                return; // Prevent the function from executing further if edittype is missing.
            }

            let dataarray = {
                studiokey: studiokey,
                orderid: orderid,
                ordertypekey: ordertypekey,
                ordertype: ordertype,
                ecorderitemmapkey: ecorderitemmapkey,
                ordertypeitemkey: ordertypeitemkey,
                originalorderkey: originalorderkey,
                edittypekey: edittype,
                lamtypekey: lamtypekey,
                customerkey: customerkey,
                isurgent: isurgent,
                discount: discount,
                paidcost: paidcost,
                softcopycount: 0,  // Fix: Use correct variable
                hardcopycount: hcopy,   // Fix: Use correct variable
                deliverydate: deliverydate,
                remarks: remarks,
                iscompleted: 0,
                _token: "{{ csrf_token() }}" // Required for Laravel AJAX requests
            };
            console.log('Data Sent:', dataarray);

            let cells = document.querySelectorAll(`#row_ec_${ecorderitemmapkey} td`);
            cells.forEach(cell => {
                if (cell.cellIndex !== 0) {
                    cell.contentEditable = "false";
                    cell.classList.remove("edit-mode");
                }
            });

            $.ajax({
                    url: "{{ route('storeOrder_ss') }}",
                    type: "POST",
                    data: dataarray,
                    success: function (response) {
                        console.log("Order Updated Successfully:", response);

                        // Update the table row with latest values
                        document.getElementById(`hcopy_ec_${ecorderitemmapkey}`).innerHTML = hcopy;
                        document.getElementById(`edittype_ec_${ecorderitemmapkey}`).innerHTML = edittypeText;

                        // Show Edit Button Again
                        document.getElementById(`editBtn_ec_${ecorderitemmapkey}`).style.display = "inline-block";
                        document.getElementById(`dltBtn_ec_${ecorderitemmapkey}`).style.display = "inline-block";
                        document.getElementById(`doneBtn_ec_${ecorderitemmapkey}`).style.display = "inline-block";
                        document.getElementById(`saveBtn_ec_${ecorderitemmapkey}`).style.display = "none";
                        // render table
                        let flashbody = '<div id="flash-message" class="alert alert-success" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);z-index: 9999; padding: 15px 20px; font-size: 16px; text-align: center;background-color: #434844; color: white; border-radius: 5px; box-shadow: 0px 4px 6px rgba(0,0,0,0.1);">'
                        + response.message +'!</div>';

                        $("body").prepend(flashbody);
                            // Automatically remove the message after 2 seconds
                            setTimeout(function() {
                                $("#flash-message").fadeOut("slow", function() {
                                    $(this).remove();
                                });
                            }, 2000);
                    },
                    error: function (xhr, status, error) {
                    console.error("Error:", xhr.responseText);

                    // Attempt to parse the JSON response
                    try {
                        var response = JSON.parse(xhr.responseText);

                        // Display the error message using SweetAlert2
                        Swal.fire({
                            title: 'Failed to Create Order',
                            text: response.message || 'An unexpected error occurred.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } catch (e) {
                        // If parsing fails, display a generic error message
                        Swal.fire({
                            title: 'Failed to Create Order',
                            text: 'An unexpected error occurred.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        }, 300);
    }

    // ******************* End Extra Copy Retated ************************

    // Mark as completed
    $(document).on('click', '.mark-done-btn', function() {
        const itemId = $(this).data('id');
        const orderkey = $(this).data('orderid');
        const $button = $(this);

        // Optional: Confirm before marking as done
        Swal.fire({
            title: "Mark as Complete?",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#0cc584",
            confirmButtonText: "Yes",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.isConfirmed) {
                // Example AJAX call (update iscompleted in DB)
                $.ajax({
                    url: `/update-complete-status/${itemId}`,
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'), // CSRF token
                        orderkey: orderkey
                    },
                    success: function(response) {
                        // Change color if update is successful
                        $button
                            .removeClass('btn-secondary')
                            .addClass('btn-done')
                            .attr('style', '') // Remove gray style if any

                        Swal.fire("Marked as complete!", "", "success");
                        checkAllItemsCompleted(orderkey);
                    },
                    error: function(xhr) {
                        Swal.fire("Error!", "Could not update item.", "error");
                    }
                });
            }
        });
    });

    //  Check all order item complete
    function checkAllItemsCompleted(orderkey) {
        $.ajax({
            url: `/check-all-items-completed/${orderkey}`,
            type: "GET",
            success: function(response) {
                if (response.allCompleted) {
                    updateOrderAsCompleted(orderkey);
                }
            },
            error: function() {
                console.error("Error checking item completion status.");
            }
        });
    }
    // Update Order status
    function updateOrderAsCompleted(orderkey) {
        $.ajax({
            url: `/update-order-complete`,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                orderkey: orderkey
            },
            success: function(response) {
                if (response.status === "success") {
                    orderUpdated = true; // 
                    Swal.fire("Order Completed!", "All items are done.", "success");
                    // 🔄 Fetch latest order summary (status, total, paid, etc.)
                    $.ajax({
                        url: `/get-order-details/${orderkey}`,
                        type: "GET",
                        success: function(orderdetail) {
                            // Now update modal fields with latest data      
                            order=orderdetail[0]
                            let ordertype=order.order_type
                            if (ordertype === 'Studio Sittings') {
                                setTimeout(() => {
                                    $('#orderModal_SS').modal('show');                                      
                                    $('#status').text(order.salestatus);
                                    $('#total').text(order.totalcost);
                                    $('#paid').text(order.paidcost);
                                    $('#discount').text(order.discount);
                                    $('#urgent').text(order.isurgent == 1 ? 'Yes' : 'No');
                                    loaditemdata_SS(orderkey);
                                }, 500);
                            }
                            if (ordertype === 'Extra Copy') {
                                setTimeout(() => {
                                    $("#orderModal_EC").modal("show");
                                    $("#total_EC").text(order.totalcost);
                                    $("#discount_EC").text(order.discount);
                                    $("#paid_EC").text(order.paidcost);
                                    $("#urgent_EC").text(order.isurgent == 1 ? 'Yes' : 'No');
                                    $("#status_EC").text(order.salestatus);
                                    loaditemdata_EC(orderkey)
                                }, 500);
                            }
                            if (ordertype === 'Frames') {
                                setTimeout(() => {
                                    $("#orderModal_FR").modal("show");
                                    $("#total_fr").text(order.totalcost);
                                    $("#discount_fr").text(order.discount);
                                    $("#paid_fr").text(order.paidcost);
                                    $("#urgent_fr").text(order.isurgent == 1 ? 'Yes' : 'No');
                                    $("#status_fr").text(order.salestatus);
                                    loaditemdata_FR(orderkey)
                                }, 500);
                            }
                            if (ordertype === 'Media') {
                                setTimeout(() => {
                                    $("#orderModal_ME").modal("show");
                                    $("#total_me").text(order.totalcost);
                                    $("#discount_me").text(order.discount);
                                    $("#paid_me").text(order.paidcost);
                                    $("#urgent_me").text(order.isurgent == 1 ? 'Yes' : 'No');
                                    $("#status_me").text(order.salestatus);
                                    loaditemdata_ME(orderkey)
                                }, 500);
                            }
                        },
                        error: function() {
                            console.error("Failed to fetch updated order info.");
                        }
                    });
                }
            },
            error: function() {
                Swal.fire("Error", "Could not update order status.", "error");
            }
        });
    }

    // Remove order from summary table
    $(document).on('click', '.remove-order', function(event) {
        event.preventDefault();

        let orderItemId = $(this).data('id');
        let orderkey = $(this).data('orderid');
        let ordertype = $(this).data('type');

        Swal.fire({
            title: "Are you sure?",
            text: "Do you want to delete this order item?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/delete-order-item/" + orderItemId,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}",
                        orderkey: orderkey
                    },
                    success: function(response) {
                        $("body").prepend(`
                            <div id="flash-message" class="alert alert-success"
                                style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
                                z-index: 9999; padding: 15px 20px; font-size: 16px; text-align: center;
                                background-color: #434844; color: white; border-radius: 5px; box-shadow: 0px 4px 6px rgba(0,0,0,0.1);">
                                Order item deleted!
                            </div>
                        `);

                        // Refresh modal
                        if (ordertype === 'Studio Sittings') {
                            setTimeout(() => {
                                loaditemdata_SS(orderkey);
                                $('#orderviewmodal_SS').modal('show'); // ✅ fixed ID selector
                            }, 500);
                        }
                        if (ordertype === 'Extra Copy') {
                            setTimeout(() => {
                                loaditemdata_EC(orderkey);
                                $('#orderviewmodal_EC').modal('show'); // ✅ fixed ID selector
                            }, 500);
                        }
                        if (ordertype === 'Frames') {
                            setTimeout(() => {
                                loaditemdata_FR(orderkey);
                                $('#orderviewmodal_FR').modal('show'); // ✅ fixed ID selector
                            }, 500);
                        }
                        if (ordertype === 'Media') {
                            setTimeout(() => {
                                loaditemdata_ME(orderkey);
                                $('#orderviewmodal_ME').modal('show'); // ✅ fixed ID selector
                            }, 500);
                        }

                        // Auto-hide the flash message
                        setTimeout(function() {
                            $("#flash-message").fadeOut("slow", function() {
                                $(this).remove();
                            });
                        }, 2000);
                    },
                    error: function() {
                        Swal.fire("Error!", "Something went wrong.", "error");
                    }
                });
            }
        });
    });

    function loadOrderTypeItems(otk) {
        var ordertypekey = otk;
        if (ordertypekey) {
            $.ajax({
                url: '/ordertypeitem/' + ordertypekey,
                type: 'GET',
                success: function (data) {
                    $('#sittingitem').empty().append('<option value="">Select an Item</option>');
                    $.each(data, function (key, item) {
                        $('#sittingitem').append('<option value="' + item.ordertypeitemkey + '">' + item.itemname + '</option>');
                    });
                },
                error: function () {
                    alert('Failed to fetch items. Please try again.');
                }
            });
        }
    }

    function vieworder(key,no,odate,customer,total,discount,paid,urgent,status,otk,ot) {
        event.preventDefault(); // Prevent default form submission
        let orderkey = $(this).data("orderkey"); // Get Order ID from button
        //alert("orderkey  no ="+key+":"+no)
        document.getElementById("otk").value=otk
        // Clear previous data and show loading placeholders
        $("#order-id").text("Loading...");
        $("#customer-name").text("Loading...");
        $("#order-total").text("Loading...");
        $("#order-items").html("<li>Loading items...</li>");

        // Show the modal first

        $("#orderModal_All").modal("show");
        $("#onum").text(no);
        $("#okey").text(key);
        $("#odate").text(odate);
        $("#customer").text(customer);
        $("#total").text(total);
        $("#discount").text(discount);
        $("#paid").text(paid);
        $("#urgent").text(urgent);
        $("#status").text(status);
        loaditemdata(key)


        // if (ot=='Studio Sittings')
        // {
        //     // $("#orderModal").modal("show");
        //     // $("#onum").text(no);
        //     // $("#okey").text(key);
        //     // $("#odate").text(odate);
        //     // $("#customer").text(customer);
        //     // $("#total").text(total);
        //     // $("#discount").text(discount);
        //     // $("#paid").text(paid);
        //     // $("#urgent").text(urgent);
        //     // $("#status").text(status);
        //     loaditemdata_SS(key)
        // }

        // if (ot=='Media')
        // {
        //     $("#orderModal_ME").modal("show");
        //     $("#onum_me").text(no);
        //     $("#okey_me").text(key);
        //     $("#odate_me").text(odate);
        //     $("#customer_me").text(customer);
        //     $("#total_me").text(total);
        //     $("#discount_me").text(discount);
        //     $("#paid_me").text(paid);
        //     $("#urgent_me").text(urgent);
        //     $("#status_me").text(status);
        //     loaditemdata_ME(key)
        // }

        // if (ot=='Frames')
        // {
        //     $("#orderModal_FR").modal("show");
        //     $("#onum_fr").text(no);
        //     $("#okey_fr").text(key);
        //     $("#odate_fr").text(odate);
        //     $("#customer_fr").text(customer);
        //     $("#total_fr").text(total);
        //     $("#discount_fr").text(discount);
        //     $("#paid_fr").text(paid);
        //     $("#urgent_fr").text(urgent);
        //     $("#status_fr").text(status);
        //     loaditemdata_FR(key)
        // }

        // if (ot=='Extra Copy')
        // {
        //     $("#orderModal_EC").modal("show");
        //     $("#onum_EC").text(no);
        //     $("#okey_EC").text(key);
        //     $("#odate_EC").text(odate);
        //     $("#customer_EC").text(customer);
        //     $("#total_EC").text(total);
        //     $("#discount_EC").text(discount);
        //     $("#paid_EC").text(paid);
        //     $("#urgent_EC").text(urgent);
        //     $("#status_EC").text(status);
        //     loaditemdata_EC(key)

        // }

        $(this).off("shown.bs.modal");

    }  
    
    $('#orderModal_All').on('hidden.bs.modal', function () {
        if (orderUpdated) {
            loaddata(); // Reload only if order was updated
            orderUpdated = false; // Reset flag
        }
    });

function printDiv(type,divId,onum,odate,dateDiv) {

    let basicDetailsElement = document.getElementById(divId);
    let printWindow = window.open('', '', 'width=800,height=600');

    let clonedContent = basicDetailsElement.cloneNode(true);
    if (type!='EC'){
    clonedContent.querySelector("#addnew").remove();}
    clonedContent.querySelector("#"+dateDiv).remove();
    clonedContent.querySelector("div[id^='date']").remove();

    clonedContent.querySelectorAll("tr[id^='row_']").forEach(row => {
       //if (row.cells.length > 5) { // Ensure the cell exists before deleting
       if (type=='FR'){
        row.deleteCell(7);
       }
        else{row.deleteCell(6);}
       //}
    });

    printWindow.document.write(`
    <html>
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            background: #f5f5f5;
        }
        .invoice-container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .company-logo img {
            max-width: 100px;
        }
        .invoice-details {
            text-align: right;
        }
        .invoice-details h2 {
            margin: 0;
            color: #333;
        }
        .client-info, .invoice-summary {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }

        .total {
            text-align: right;
        }
        .print-btn {
            display: block;
            width: 100px;
            margin: 20px auto;
            padding: 10px;
            background: #007bff;
            color: white;
            text-align: center;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .print-btn:hover {
            background: #0056b3;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        @media print {
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="invoice-container" id="invoice">
    <div class="invoice-header">
        <div class="company-logo">
            <img id="slogo" src="/logo/s_`+skey+`.png" alt="Company Logo">
        </div>
        <div class="">
            <h3>`+sname+` Studio</h3>
            <h5>`+saddress+`</h5>
            <h5>T.P. `+sphone+`</h5>

        </div>
        <div class="invoice-details">
            <h2>INVOICE</h2>
            <p>Invoice #: `+onum+`</p>
            <p>Date: `+odate+`</p>
        </div>
    </div>
    ${clonedContent.innerHTML}
    <div class="invoice-summary">
        <!-- <h3 class="total">Grand Total: $180.00</h3> -->
    </div>
</div>



</body>
</html>

    `);


    printWindow.document.close();
    printWindow.focus();

    // Wait for the new window to load before printing
    printWindow.onload = function () {

        //$('#slogo').attr('src','/logo/s_'+skey+'.png')
        printWindow.print();
        printWindow.onafterprint = function () {
            printWindow.close();
        };
    };
}

</script>
@endpush
@endsection
