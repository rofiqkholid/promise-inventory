<div id="modal-delete" class="modal-container hidden">
    <div class="relative p-4 w-full max-w-md">
        <div class="relative p-4 text-center bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
            <button type="button" class="close-modal text-gray-400 absolute top-2.5 right-2.5 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 dark:hover:bg-gray-600 dark:hover:text-white">
                <i class="fa-solid fa-xmark w-5 h-5"></i>
            </button>
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-3.5">
                <i class="fa-solid fa-trash-can text-gray-400 dark:text-gray-500 text-4xl"></i>
            </div>
            <p class="mb-4 text-gray-500 dark:text-gray-300">Are you sure you want to delete this item?</p>
            <div class="flex justify-center gap-4">
                <button type="button" class="close-modal px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-600">No, cancel</button>
                <button type="button" id="confirmDelete" class="px-3 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">Yes, I'm sure</button>
            </div>
        </div>
    </div>
</div>

<style>
.modal-container {
    position: fixed;
    top: 0;
    right: 0;
    left: 0;
    bottom: 0;
    z-index: 50;
    display: none;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
    background-color: rgb(15 23 42 / 0.5);
    backdrop-filter: blur(4px);
}
.modal-container:not(.hidden) {
    display: flex;
}
.error-msg {
    margin-top: 0.25rem;
    font-size: 0.75rem;
    line-height: 1rem;
    color: rgb(239 68 68);
}
</style>
