document.addEventListener("DOMContentLoaded", async () => {
  // get
  await getEmployees();
  // add
  const addEmployeeForm = document.getElementById("frm_add_employee");

  addEmployeeForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const formData = new FormData(addEmployeeForm);

    const response = await fetch(wce_object.ajax_url, {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (data.status) {
      location.reload();
    } else {
      alert("error");
    }
  });
  // delete
  const deleteButtons = document.querySelectorAll(".btn_delete_employee");
  const editButtons = document.querySelectorAll(".btn_edit_employee");

  deleteButtons.forEach((button) => {
    button.addEventListener("click", async () => {
      if (confirm("are you sure?")) {
        const buttonId = button.getAttribute("data-id");

        const response = await fetch(wce_object.ajax_url, {
          method: "post",
          body: new URLSearchParams({
            action: "wce_delete_employees_data",
            employeeId: buttonId,
          }),
        });
        const data = await response.json();

        if (data.status) {
          location.reload();
        } else {
          alert("error");
        }
      }
    });
  });

  // show the add form
  const openButtonForm = document.getElementById("btn_open_add_employee_form");
  const closeButtonForm = document.getElementById(
    "btn_close_add_employee_form"
  );

  const closeEditFormBtn = document.getElementById(
    "btn_close_edit_employee_form"
  );

  openButtonForm.addEventListener("click", () => {
    document
      .querySelector(".add-employee-form")
      .classList.remove("hide_element");
    openButtonForm.classList.add("hide_element");
  });

  closeButtonForm.addEventListener("click", () => {
    document.querySelector(".add-employee-form").classList.add("hide_element");
    openButtonForm.classList.remove("hide_element");
  });

  closeEditFormBtn.addEventListener("click", () => {
    document.querySelector(".edit-employee-form").classList.add("hide_element");
    openButtonForm.classList.remove("hide_element");
  });
  editButtons.forEach((btn) => {
    btn.addEventListener("click", async () => {
      document
        .querySelector(".edit-employee-form")
        .classList.remove("hide_element");
      openButtonForm.classList.add("hide_element");

      const employeeId = btn.getAttribute("data-id");
      const respone = await fetch(wce_object.ajax_url, {
        method: "post",
        body: new URLSearchParams({
          action: "wce_get_employee_data",
          employeeId: employeeId,
        }),
      });
      const data = await respone.json();

      document.getElementById("employee_name").value = data?.data?.name;
      document.getElementById("employee_email").value = data?.data?.email;
      document.getElementById("employee_designation").value =
        data?.data?.designation;
      document.getElementById("edit_form_image").src =
        data?.data?.profile_image;
      document.getElementById("employee_id").value = data?.data?.id;
    });
  });

  const editForm = document.getElementById("frm_edit_employee");
  editForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(editForm);
    const response = await fetch(wce_object.ajax_url, {
      method: "post",
      body: formData,
    });
    const data = await response.json();

    if (data.status) {
      location.reload();
    } else {
      alert("error");
    }
  });
});

async function getEmployees() {
  document.getElementById("employees_data_tbody").innerHTML = "";
  const response = await fetch(wce_object.ajax_url, {
    method: "POST",
    body: new URLSearchParams({
      action: "wce_load_employees_data",
    }),
  });
  const data = await response.json();
  const { employees } = data;

  employees.forEach((employee, id) => {
    let employeeProfileImage = "--";

    if (employee.profile_image) {
      employeeProfileImage = `<img src="${employee.profile_image}" height="80px" width="80px"/>`;
    }

    document.getElementById("employees_data_tbody").innerHTML += `
        <tr>
            <td>${id + 1}</td>
            <td>${employee.name}</td>
            <td>${employee.email}</td>
            <td>${employee.designation}</td>
            <td>${employeeProfileImage}</td>
            <td>
                <button data-id="${
                  employee.id
                }" class="btn_edit_employee">Edit</button>
                <button data-id="${
                  employee.id
                }" class="btn_delete_employee">Delete</button>
            </td>
        </tr>
    `;
  });
}
