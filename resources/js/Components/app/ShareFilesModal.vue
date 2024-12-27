<template>
    <modal :show="props.modelValue" @show="onShow" max-width="sm">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-200">
                Share Files
            </h2>
            <div class="mt-6">
                <InputLabel
                    for="shareEmail"
                    value="Enter Email Addresses"
                    class="sr-only"
                />

                <TextInput
                    type="text"
                    ref="emailInput"
                    id="shareEmail"
                    v-model="form.email"
                    class="mt-1 block w-full"
                    :class="
                        form.errors.email
                            ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                            : ''
                    "
                    placeholder="Enter email addresses"
                    @keyup.enter="share"
                />
                <InputError :message="form.errors.email" class="mt-2" />
            </div>
            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                <PrimaryButton
                    class="ml-3"
                    :class="{ 'opacity-25': form.processing }"
                    @click="share"
                    :disable="form.processing"
                >
                    Share
                </PrimaryButton>
            </div>
        </div>
    </modal>
</template>

<script setup>
// Imports
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { nextTick, ref } from 'vue';
import { emitter } from '@/event-bus';

// Uses
const form = useForm({
    email: null,
    all: false,
    ids: [],
    parent_id: null,
});

// Refs
const emailInput = ref(null);

// Props & Emit
const props = defineProps({
    modelValue: Boolean,
    allSelected: Boolean,
    selectedIds: Array,
});

const modelValue = ref(props.modelValue);

const emit = defineEmits(['update:modelValue']);
const page = usePage();

// Computed

// Methods
function onShow() {
    nextTick(() => emailInput.value.focus());
}

function share() {
    form.parent_id = page.props.folder.id;
    if (props.allSelected) {
        form.all = true;
        form.ids = [];
    } else {
        form.ids = props.selectedIds;
        form.all = false;
    }
    form.post(route('file.share'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            // Show success notification
            emitter.emit('show-notif', {
                message: `Selected files were shared`,
                type: 'success',
            });
            form.reset();
        },
        onError: (e) => {
            emailInput.value.focus();
            console.log(e);
        },
    });
}

function closeModal() {
    emit('update:modelValue', false);
    form.clearErrors();
    form.reset();
}

// Hooks
</script>

<style scoped></style>
