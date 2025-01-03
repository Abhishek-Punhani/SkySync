<template>
    <modal :show="modelValue" @show="onShow" max-width="sm">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-200">
                Create New Folder
            </h2>
            <div class="mt-6">
                <InputLabel
                    for="folderName"
                    value="Folder Name"
                    class="sr-only"
                />

                <TextInput
                    type="text"
                    ref="folderNameInput"
                    id="folderName"
                    v-model="form.name"
                    class="mt-1 block w-full"
                    :class="
                        form.errors.name
                            ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                            : ''
                    "
                    placeholder="Folder Name"
                    @keyup.enter="createFolder"
                />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>
            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                <PrimaryButton
                    class="ml-3"
                    :class="{ 'opacity-25': form.processing }"
                    @click="createFolder"
                    :disable="form.processing"
                >
                    Submit
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
// import { showSuccessNotification } from '@/event-bus.js';

// Uses
const form = useForm({
    name: '',
    parent_id: null,
});

// Refs
const folderNameInput = ref(null);

// Props & Emit
const { modelValue, closeDropdown } = defineProps({
    modelValue: Boolean,
    closeDropdown: Function,
});
const emit = defineEmits(['update:modelValue']);
const page = usePage();

// Computed

// Methods
function onShow() {
    nextTick(() => folderNameInput.value.focus());
}

function createFolder() {
    form.parent_id = page.props.folder.id;
    const name = form.name.trim();
    form.post(route('folder.create'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            emitter.emit('show-notif', {
                message: `The folder "${name}" was created`,
                type: 'success',
            });
            form.reset();
            closeDropdown();
        },
        onError: (e) => {
            folderNameInput.value.focus();
            console.log(e);
        },
    });
}

function closeModal() {
    emit('update:modelValue');
    form.clearErrors();
    form.reset();
}

// Hooks
</script>

<style scoped></style>
