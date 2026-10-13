import './Enrollments.css';

function Enrollments() {
const enrollments = [
{
id: 1,
course: 'Database Systems',
code: 'SE202',
status: 'Pending',
},
{
id: 2,
course: 'Web Development',
code: 'SE203',
status: 'Approved',
},
{
id: 3,
course: 'Data Structures',
code: 'SE204',
status: 'Rejected',
},
];

  return (
    <div className="enrollments-page">
      <h1>My Enrollments</h1>
      <p>Check the status of your course enrollment requests.</p>

      <div className="enrollments-list">
        {enrollments.map((enrollment) => (
          <div className="enrollment-card" key={enrollment.id}>
            <div>
              <h3>{enrollment.course}</h3>
              <p>Course Code: {enrollment.code}</p>
            </div>

            <span
              className={`enrollment-status ${enrollment.status.toLowerCase()}`}
            >
              {enrollment.status}
            </span>
          </div>
        ))}
      </div>
    </div>
  );
}

export default Enrollments;
