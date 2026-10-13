import './Courses.css';

function Courses() {
const courses = [
{
id: 1,
title: 'Introduction to Programming',
code: 'SE101',
lecturer: 'Abebe Kebede',
},
{
id: 2,
title: 'Database Systems',
code: 'SE202',
lecturer: 'Sara Ahmed',
},
{
id: 3,
title: 'Web Development',
code: 'SE203',
lecturer: 'Dawit Tadesse',
},
{
id: 4,
title: 'Data Structures and Algorithms',
code: 'SE204',
lecturer: 'Meron Tesfaye',
},
];

  return (
    <div className="courses-page">
      <header className="courses-header">
        <h1>Available Courses</h1>
        <p>Explore courses offered at CampusConnect.</p>
      </header>

      <div className="courses-grid">
        {courses.map((course) => (
          <div className="course-card" key={course.id}>
            <h2>{course.title}</h2>
            <p>
              <strong>Course Code:</strong> {course.code}
            </p>
            <p>
              <strong>Lecturer:</strong> {course.lecturer}
            </p>

            <button type="button" className="enroll-button">
              Request Enrollment
            </button>
          </div>
        ))}
      </div>
    </div>
  );
}

export default Courses;
