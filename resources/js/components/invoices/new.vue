
<script setup>
import { useRouter } from "vue-router";
import { onMounted, ref, watch } from 'vue';
const router = useRouter()
import axios from 'axios';


let customers = ref([])
const items = ref([]);
const discount = ref('');

const successMessage = ref('');
const errorMessage = ref('');


const addNewLine = () => {
    items.value.push({
        id: Math.random(), // Generate a unique ID for each item (You can use your own method for generating IDs)
        description: '',
        unitPrice: '',
        quantity: '',
        total: 0
    });
};

const removeItem = (index) => {
    items.value.splice(index, 1);
};


const calculateTotal = (item) => {
    return item.unitPrice * item.quantity;
};

const calculateSubtotal = () => {
    return items.value.reduce((subtotal, item) => subtotal + calculateTotal(item), 0);
};


const calculateSubtotalGrand = () => {
    var total = items.value.reduce((subtotal, item) => subtotal + calculateTotal(item), 0);
    if (discount.value) {
        const discountAmount = (discount.value / 100) * total;
        total = total - discountAmount
    }
    if (total < 0) {

        return 0
    } else {
        return total
    }
};


const calculateGrandTotal = ref(calculateSubtotalGrand());

watch(discount, (newDiscount) => {
    const discountValue = parseFloat(newDiscount); // Parse the discount value as a number

    if (isNaN(discountValue) || discountValue <= 0 || discountValue >= 100) {
        calculateGrandTotal.value = calculateSubtotalGrand();
    } else {
        const discountAmount = (discountValue / 100) * calculateSubtotalGrand();
        calculateGrandTotal.value = calculateSubtotalGrand() - discountAmount;
    }
});

const closeModal = () => {


    if (successMessage.value) {
        router.push('/');
        errorMessage.value = '';
    }

    if (errorMessage.value) {
        errorMessage.value = '';
    }
};

onMounted(async () => {
    getCustomers();
})

const getCustomers = async () => {
    let response = await axios.get("/api/get_cusomers")
    customers.value = response.data.customers
}

const newInvoice = async () => {

    const customer_id = document.getElementById('customer_id').value;
    const date = document.getElementById('date').value;
    const due_date = document.getElementById('due_date').value;
    const number = document.querySelector('input[name="number"]').value;
    const reference = document.querySelector('input[name="reference"]').value;
    const terms = document.querySelector('textarea[name="terms"]').value;
    const discount = document.querySelector('input[name="discount"]').value;
    const sub_total = document.querySelector('input[name="sub_total"]').value;
    const total = document.querySelector('input[name="total"]').value;




    // Extracting the data from the items array
    const itemsData = items.value.map((item) => {
        return {
            description: item.description,
            unitPrice: item.unitPrice,
            quantity: item.quantity,
            total: calculateTotal(item), // You can calculate the total for each item here if needed
        };
    });


    const data = {
        customer_id,
        date,
        due_date,
        number,
        reference,
        discount: discount.value,
        discount: discount,
        terms,
        sub_total,
        total,
        items: itemsData, // Adding the items data to the main data object
    };

    try {
        const response = await axios.post("/api/create_invoice", data);
        if (response.data) {
            successMessage.value = 'Invoice created successfully!';
            setTimeout(() => {
                router.push('/');
            }, 5000);
        } else {
            errorMessage.value = 'Failed to create invoice. Please try again.';


        }

    } catch (error) {
        console.error('Error creating invoice:', error);
    }


}




</script>

<template>
    <div class="container">




        <div class="modal" v-if="successMessage || errorMessage">
            <div class="modal__content"
                :class="{ 'modal__content--success': successMessage, 'modal__content--error': errorMessage }">
                <p v-if="successMessage">{{ successMessage }}</p>
                <p v-if="errorMessage">{{ errorMessage }}</p>
                <button @click="closeModal" class="modal__close-btn">Close</button>

            </div>
        </div>


        <div class="invoices">

            <div class="card__header">

                <div>
                    <h2 class="invoice__title">New Invoice</h2>
                </div>
                <div>

                </div>
            </div>

            <div class="card__content">
                <div class="card__content--header">
                    <div>
                        <p class="my-1">Customer</p>
                        <select name="customer_id" id="customer_id" class="input" v-if="customers.length > 0">
                            <option>Select Customer</option>
                            <option v-for="(customer, index) in customers" :key="index" :value="customer.id">
                                {{ customer.firstname }} {{ customer.lastname }}
                            </option>
                        </select>
                        <select name="customer_id" id="customer_id" class="input" v-else>
                            <option value="0">Select Customer</option>
                        </select>
                    </div>
                    <div>
                        <p class="my-1">Date</p>
                        <input id="date" placeholder="dd-mm-yyyy" name="date" type="date" class="input"> <!---->
                        <p class="my-1">Due Date</p>
                        <input id="due_date" name="due_date" type="date" class="input">
                    </div>
                    <div>
                        <p class="my-1">Number</p>
                        <input type="text" name="number" class="input">
                        <p class="my-1">Reference(Optional)</p>
                        <input type="text" name="reference" class="input">
                    </div>
                </div>
                <br><br>
                <div class="table">

                    <div class="table--heading2">
                        <p>Item Description</p>
                        <p>Unit Price</p>
                        <p>Qty</p>
                        <p>Total</p>
                        <p></p>
                    </div>

                    <!-- item 1 -->
                    <div class="table--items2" v-for="(item, index) in items" :key="index + 1">
                        <p>#{{ index + 1 }} <input type="text" class="input" style="max-width: 400px;margin: 0 25px;"
                                v-model="item.description"></p>
                        <p>
                            <input type="text" class="input" v-model="item.unitPrice">
                        </p>
                        <p>
                            <input type="text" class="input" v-model="item.quantity">
                        </p>
                        <p>{{ calculateTotal(item) }}</p>
                        <p style="color: red; font-size: 24px; cursor: pointer;" @click="removeItem(index)">&times;</p>
                    </div>

                    <div style="padding: 10px 30px !important;">
                        <button class="btn btn-sm btn__open--modal" @click="addNewLine">Add New Line</button>
                    </div>
                </div>

                <div class="table__footer">
                    <div class="document-footer">
                        <p>Terms and Conditions</p>
                        <textarea cols="50" rows="7" name="terms" class="textarea"></textarea>
                    </div>
                    <div>
                        <div class="table__footer--subtotal">
                            <p>Sub Total</p>
                            <span>$ {{ calculateSubtotal() }}</span>
                            <input type="hidden" name="sub_total" :value="calculateSubtotal()">
                        </div>
                        <div class="table__footer--discount">
                            <p>Discount</p>
                            <input type="text" name="discount" class="input" v-model="discount">
                        </div>
                        <div class="table__footer--total">
                            <p>Grand Total</p>
                            <span>$ {{ calculateSubtotalGrand() }}</span>
                            <input type="hidden" name="total" :value="calculateSubtotalGrand()">

                        </div>
                    </div>
                </div>


            </div>
            <div class="card__header" style="margin-top: 40px;">
                <div>

                </div>
                <div>
                    <a class="btn btn-secondary" @click="newInvoice()">
                        Save
                    </a>
                </div>
            </div>

        </div>

    </div>
</template>




