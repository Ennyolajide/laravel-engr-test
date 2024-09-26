<template>
    <div class="container mx-auto py-4">
        <h1 class="text-4xl text-center font-bold mb-4">Submit An Order</h1>
        <div class="lg:m-20">

            <form @submit.prevent="submitOrder">
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="form-group">
                        <label for="hmo_code">HMO Code</label>
                        <input v-model="hmoCode" type="text" class="form-control" id="hmo_code" required />
                    </div>

                    <div class="form-group">
                        <label for="provider">Provider</label>
                        <input v-model="provider" type="text" class="form-control" id="provider" required />
                    </div>

                    <div class="form-group">
                        <label for="encounter_date">Encounter Date</label>
                        <input v-model="encounterDate" type="date" class="form-control" id="encounter_date" required />
                    </div>
                </div>

                <div v-for="(item, index) in items" :key="index" class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <div class="grid grid-cols-6 gap-4 mb-4">
                            <div class="col-span-1">
                                <label for="sn">S/N</label>
                                <div class="text-blue-500 font-bold mt-3">{{ index + 1 }}</div>
                            </div>
                            <div class="form-group col-span-5">
                                <label for="item_name">Item Name</label>
                                <input v-model="item.name" type="text" class="form-control" required />
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="form-group col-span-1">
                                <label for="item_unit_price">Unit Price</label>
                                <input v-model.number="item.unit_price" type="number" class="form-control" required />
                            </div>

                            <div class="form-group col-span-1">
                                <label for="item_quantity">Quantity</label>
                                <input v-model.number="item.quantity" type="number" class="form-control" required />
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="grid grid-cols-6 gap-4 mb-4">
                            <div class="form-group col-span-5">
                                <label for="amount">Sub Total</label>
                                <input :value="item.unit_price * item.quantity" type="number" class="form-control"
                                    required readonly />
                            </div>
                            <div class="flex items-center col-span-1 ml-5">
                                <button type="button" @click="removeItem(index)" class="text-red-500 mt-3">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-5">
                    <div class="col-span-2">
                        <div class="grid grid-cols-5 gap-4 mb-5">
                            <div class="col-span-4 mt-3">
                                <button type="button" @click="addItem" class="text-blue-700 font-extrabold mb-4">
                                    Add <i class="fas fa-add"></i>
                                </button>
                            </div>
                            <div class="col-span-1 mt-3">
                                <label for="total">Total</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group col-span-1">
                        <input :value="totalAmount" type="number" class="form-control" readonly required />
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-5">
                    <div class="col-span-2 mt-3"></div>
                    <div class="flex justify-end mt-4">
                        <button type="submit"
                            class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-opacity-75">
                            Submit Order
                        </button>

                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import 'vue3-select/dist/vue3-select.css';
import '@fortawesome/fontawesome-free/css/all.css';
import { useToast } from 'vue-toastification';  // Import Vue Toastification

const toast = useToast();  // Create a toast instance

const errors = ref({});
const hmoCode = ref('');
const provider = ref('');
const encounterDate = ref('');
const items = ref([{ name: '', unit_price: 0, quantity: 1 }]);

const totalAmount = computed(() => {
    return items.value.reduce((total, item) => total + (item.unit_price * item.quantity), 0);
});

const addItem = () => {
    items.value.push({ name: '', unit_price: 0, quantity: 1 });
};

const removeItem = (index) => {
    items.value.splice(index, 1);
};


const submitOrder = async () => {
    errors.value = {};  // Reset errors before submitting
    try {
        const response = await fetch('/api/orders/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                hmo_code: hmoCode.value,
                provider: provider.value,
                encounter_date: encounterDate.value,
                items: items.value,
            }),
        });

        if (response.ok) {
            toast.success('Order submitted successfully!');  // Success toast
            hmoCode.value = '';
            provider.value = '';
            encounterDate.value = '';
            items.value = [{ name: '', unit_price: 0, quantity: 1 }];
        } else {
            const errorData = await response.json();
            if (errorData.errors) {
                errors.value = errorData.errors;  // Populate server validation errors
                // Loop through errors and show them as toast messages
                Object.values(errorData.errors).forEach((error) => {
                    toast.error(error[0]);  // Show error message in toast
                });
            }
        }
    } catch (error) {
        toast.error('Failed to submit order. Please try again.');  // Error toast
    }
};
</script>

<style scoped>
/* Style for form controls */
.form-control {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
}

/* Flexbox for Subtotal and Remove Icon */
.flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.text-red-500 {
    color: #f56565;
}
</style>
