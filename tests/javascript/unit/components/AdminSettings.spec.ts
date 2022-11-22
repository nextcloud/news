import axios from "@nextcloud/axios";
import { shallowMount, Wrapper } from "@vue/test-utils";
import { store, localVue } from "../setupStore";
import { showError, showSuccess } from "@nextcloud/dialogs";
import { loadState } from "@nextcloud/initial-state";

import 'regenerator-runtime/runtime' // NOTE: Required for testing password-confirmation?
import AdminSettings from "Components/AdminSettings.vue";


jest.mock("@nextcloud/axios");
jest.mock("@nextcloud/initial-state");
jest.mock("@nextcloud/router");
jest.mock("@nextcloud/dialogs");

describe("AdminSettings.vue", () => {
	"use strict";

	let wrapper: Wrapper<AdminSettings>;

	beforeAll(() => {
		jest.useFakeTimers();
		(loadState as any).mockReturnValue('');
		wrapper = shallowMount(AdminSettings, { localVue, store});
	});

	it("should initialize and fetch settings from state", () => {
		expect(loadState).toBeCalledTimes(7);
	});

	it("should send post with updated settings", async () => {
		jest.spyOn(axios, "post").mockResolvedValue({ data: {} });
		(wrapper.vm as any).handleResponse = jest.fn()

		await wrapper.vm.$options?.methods?.update.call(wrapper.vm, 'key', 'val');

		expect(axios.post).toBeCalledTimes(1);
	});

	it("should handle bad response", () => {
		(showError as any).mockClear();
		console.error = jest.fn();
		wrapper.vm.$options?.methods?.handleResponse.call(wrapper.vm, {
			error: true,
			errorMessage: "FAIL",
		});

		expect(showError).toBeCalledTimes(1);
	});

	it("should handle success response", () => {
		wrapper.vm.$options?.methods?.handleResponse.call(wrapper.vm, {
			status: "ok",
		});
		(global as any).t = jest.fn();
		jest.runAllTimers();

		expect(showSuccess).toBeCalledTimes(1);
	});

	afterAll(() => {
		jest.clearAllMocks();
		jest.useRealTimers();
	});
});
