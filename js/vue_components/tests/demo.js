import { mount } from '@vue/test-utils'

import component from '../../vue_components/component.vue'

test('does a thing', () => {
  const wrapper = mount(component, {
    propsData: {
      oldestFirst: true
    }
  });
  expect(wrapper.props().oldestFirst).toBe(true);
  expect(wrapper.text()).toContain('asdf');
});
